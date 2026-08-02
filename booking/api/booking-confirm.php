<?php
declare(strict_types=1);

ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
error_reporting(E_ALL);

ob_start();

require_once __DIR__ . '/../config/booking.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/mailer.php';

ob_end_clean();

header('Content-Type: application/json; charset=UTF-8');

set_exception_handler(function (Throwable $e) {
    while (ob_get_level()) ob_end_clean();
    header('Content-Type: application/json; charset=UTF-8');
    http_response_code(500);
    echo json_encode([
        'ok'    => false,
        'error' => 'Server error: ' . $e->getMessage(),
        'file'  => basename($e->getFile()) . ':' . $e->getLine(),
    ]);
    exit;
});

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
    exit;
}

// ── CSRF Validation ───────────────────────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$submitted_token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
$expected_token  = $_SESSION['csrf_token'] ?? '';

if (
    empty($submitted_token) ||
    empty($expected_token)  ||
    !hash_equals($expected_token, $submitted_token)
) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'Invalid request']);
    exit;
}

$body      = json_decode(file_get_contents('php://input'), true);
$reference = trim($body['reference']       ?? '');
$pp_order  = trim($body['paypal_order_id'] ?? '');

if (!$reference || !$pp_order) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Missing reference or PayPal order ID']);
    exit;
}

$booking = qmx_get_booking($reference);

if (!$booking) {
    http_response_code(404);
    echo json_encode(['ok' => false, 'error' => 'Booking not found: ' . $reference]);
    exit;
}

if ($booking['status'] === 'confirmed') {
    echo json_encode(['ok' => true, 'reference' => $reference, 'already_confirmed' => true]);
    exit;
}

$confirmed = qmx_confirm_booking($reference, $pp_order);

if (!$confirmed) {
    $booking = qmx_get_booking($reference);
    if ($booking && $booking['status'] === 'confirmed') {
        echo json_encode(['ok' => true, 'reference' => $reference, 'already_confirmed' => true]);
    } else {
        http_response_code(500);
        echo json_encode(['ok' => false, 'error' => 'DB update failed. Status: ' . ($booking['status'] ?? 'unknown')]);
    }
    exit;
}

$booking    = qmx_get_booking($reference);
$emailError = null;
try {
    qmx_send_ticket($booking);
    qmx_mark_ticket_sent($reference);
} catch (Throwable $e) {
    $emailError = $e->getMessage();
    error_log('[QMX] Ticket email failed for ' . $reference . ': ' . $emailError);
}

echo json_encode([
    'ok'          => true,
    'reference'   => $reference,
    'message'     => 'Booking confirmed! Check your email for your ticket.',
    'email_error' => $emailError,
]);