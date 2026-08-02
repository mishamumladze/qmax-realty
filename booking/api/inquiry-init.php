<?php
/**
 * QMAX Realty — Inquiry Init API
 * POST /booking/api/inquiry-init.php
 *
 * Accepts a property viewing / general inquiry form submission,
 * validates every field, writes a row to the `inquiries` table,
 * fires both a customer confirmation e-mail and an internal
 * notification, and returns a JSON payload the frontend can use
 * to show a success state.
 *
 * Replaces the old tour booking-init.php entirely.
 */
declare(strict_types=1);

ini_set('display_errors',         '0');
ini_set('display_startup_errors', '0');
error_reporting(E_ALL);

ob_start();
require_once __DIR__ . '/../config/booking.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/mailer.php';
ob_end_clean();

header('Content-Type: application/json; charset=UTF-8');

// ── Global exception handler ──────────────────────────────────────────────────
set_exception_handler(function (Throwable $e): void {
    while (ob_get_level()) ob_end_clean();
    header('Content-Type: application/json; charset=UTF-8');
    http_response_code(500);
    echo json_encode([
        'ok'    => false,
        'error' => 'Server error: ' . $e->getMessage(),
        'trace' => basename($e->getFile()) . ':' . $e->getLine(),
    ]);
    exit;
});

// ── Method guard ──────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
    exit;
}

// ── CSRF validation ───────────────────────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$submitted_token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
$expected_token  = $_SESSION['csrf_token']        ?? '';
if (
    empty($submitted_token)  ||
    empty($expected_token)   ||
    !hash_equals($expected_token, $submitted_token)
) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'Invalid or missing CSRF token']);
    exit;
}

// ── IP-based rate limiting (1 submission per 15 seconds per IP) ───────────────
$client_ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$rate_file = sys_get_temp_dir() . '/' . hash('sha256', 'inquiry_init_' . $client_ip) . '.tmp';
$now       = time();
$last      = 0;

if (file_exists($rate_file)) {
    $raw = @file_get_contents($rate_file);
    if ($raw !== false) {
        $last = (int)$raw;
    }
}
if (($now - $last) < 15) {
    http_response_code(429);
    echo json_encode(['ok' => false, 'error' => 'Too many requests. Please wait a moment and try again.']);
    exit;
}
@file_put_contents($rate_file, (string)$now, LOCK_EX);

// ── Parse request body ────────────────────────────────────────────────────────
$body = json_decode(file_get_contents('php://input'), true);
if (!is_array($body) || empty($body)) {
    $body = $_POST;
}

// ── Load property catalogue ───────────────────────────────────────────────────
require_once __DIR__ . '/../../config/app.php';
$all_properties = require __DIR__ . '/../../config/properties.php';

// ── Validate: property slug ───────────────────────────────────────────────────
$property_slug = trim($body['property_slug'] ?? '');
$property      = $all_properties[$property_slug] ?? null;

if (!$property) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Unknown property. Please refresh and try again.']);
    exit;
}

// ── Validate: inquiry type ────────────────────────────────────────────────────
$allowed_inquiry_types = ['viewing', 'information', 'offer'];
$inquiry_type = trim($body['inquiry_type'] ?? 'viewing');
if (!in_array($inquiry_type, $allowed_inquiry_types, true)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Invalid inquiry type.']);
    exit;
}

// ── Validate: contact fields ──────────────────────────────────────────────────
$first_name = trim($body['first_name'] ?? '');
$last_name  = trim($body['last_name']  ?? '');
$email      = filter_var(trim($body['email'] ?? ''), FILTER_VALIDATE_EMAIL);
$phone      = trim($body['phone'] ?? '');
$language   = trim($body['language'] ?? 'English');
$notes      = trim($body['notes'] ?? '');

if (!$first_name || !$last_name) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'First and last name are required.']);
    exit;
}
if (mb_strlen($first_name) > 80 || mb_strlen($last_name) > 80) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Name is too long.']);
    exit;
}
if (!$email) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'A valid email address is required.']);
    exit;
}
if (!$phone || mb_strlen($phone) < 6) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'A valid phone number is required.']);
    exit;
}
if (!in_array($language, ['English', 'Russian'], true)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Please select a preferred language.']);
    exit;
}
if (mb_strlen($notes) > 2000) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Notes must be under 2000 characters.']);
    exit;
}

// ── Validate: optional preferred viewing date ─────────────────────────────────
// Only enforced when inquiry_type === 'viewing' AND a date was supplied.
$viewing_date = null;
$viewing_time = null;

$raw_date = trim($body['viewing_date'] ?? '');
if ($raw_date !== '') {
    $date_ts  = strtotime($raw_date);
    $today_ts = strtotime('today');
    $max_ts   = strtotime('+365 days', $today_ts);

    if (!$date_ts || $date_ts < $today_ts || $date_ts > $max_ts) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Please choose a viewing date between today and one year from now.']);
        exit;
    }
    $viewing_date = date('Y-m-d', $date_ts);
}

// Optional preferred time slot (morning / afternoon / evening)
$allowed_slots = ['morning', 'afternoon', 'evening', ''];
$raw_time = trim($body['viewing_time'] ?? '');
if (!in_array($raw_time, $allowed_slots, true)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Invalid time slot.']);
    exit;
}
$viewing_time = $raw_time ?: null;

// ── Write to database ─────────────────────────────────────────────────────────
$reference = qmx_create_inquiry([
    'property_slug'  => $property_slug,
    'property_title' => $property['title'],
    'property_type'  => $property['type']  ?? $property['property_type'] ?? 'property',
    'inquiry_type'   => $inquiry_type,
    'viewing_date'   => $viewing_date,
    'viewing_time'   => $viewing_time,
    'first_name'     => $first_name,
    'last_name'      => $last_name,
    'email'          => $email,
    'phone'          => $phone,
    'language'       => $language,
    'notes'          => $notes,
    'created_by'     => 'customer',
    'agent_code'     => null,
]);

// ── Send e-mails (non-fatal — log and continue) ───────────────────────────────
$email_error = null;
try {
    $inquiry_record = qmx_get_inquiry($reference);
    if ($inquiry_record) {
        qmx_send_inquiry_confirmation($inquiry_record);
    }
} catch (Throwable $e) {
    $email_error = $e->getMessage();
    error_log('[QMX] Inquiry email failed for ' . $reference . ': ' . $email_error);
}

// ── Respond ───────────────────────────────────────────────────────────────────
echo json_encode([
    'ok'           => true,
    'reference'    => $reference,
    'property'     => $property['title'],
    'inquiry_type' => $inquiry_type,
    'viewing_date' => $viewing_date
        ? date('l, F j, Y', strtotime($viewing_date))
        : null,
    'viewing_time' => $viewing_time,
    'message'      => 'Thank you! Your inquiry has been received. We\'ll be in touch within 24 hours.',
    'email_error'  => $email_error, // null in the happy path; useful for debugging
]);