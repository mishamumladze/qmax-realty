<?php

declare(strict_types=1);

/**
 * QMAX Realty — Newsletter Subscribe Handler
 */

session_start();

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Location: /socials');
    exit;
}

// ── CSRF Token Validation ─────────────────────────────────────────────────────
$submitted_token = trim($_POST['csrf_token'] ?? '');
$expected_token  = $_SESSION['csrf_token'] ?? '';

if (
    empty($submitted_token) ||
    empty($expected_token)  ||
    !hash_equals($expected_token, $submitted_token)
) {
    http_response_code(403);
    header('Location: /socials?error=invalid_request');
    exit;
}

// Rotate CSRF token
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

// ── IP-Based Rate Limiting ────────────────────────────────────────────────────
$client_ip  = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$rate_file  = sys_get_temp_dir() . '/' . hash('sha256', 'newsletter_' . $client_ip) . '.tmp';
$now        = time();
$last       = file_exists($rate_file) ? (int)@file_get_contents($rate_file) : 0;

if (($now - $last) < 60) {
    http_response_code(429);
    header('Location: /socials?error=too_fast');
    exit;
}
@file_put_contents($rate_file, (string)$now);

// ── Email Validation ──────────────────────────────────────────────────────────
$raw_email = trim($_POST['email'] ?? '');
$email     = filter_var($raw_email, FILTER_VALIDATE_EMAIL);

if ($email === false || strlen($email) > 254) {
    http_response_code(400);
    header('Location: /socials?error=invalid_email');
    exit;
}

$email = strtolower($email);

// ── Save to SQLite ────────────────────────────────────────────────────────────
try {
    require_once __DIR__ . '/booking/includes/db.php';
    $db = qmx_db();
    // INSERT OR IGNORE so duplicate emails are silently skipped
    $db->prepare("INSERT OR IGNORE INTO newsletter (email, ip) VALUES (?, ?)")
       ->execute([$email, $client_ip]);
} catch (\Throwable $e) {
    error_log('[Newsletter] DB error: ' . $e->getMessage());
    // Non-fatal — continue to Brevo/file fallback
}

// ── Brevo integration (optional) ─────────────────────────────────────────────
$brevo_api_key = getenv('BREVO_API_KEY');
if ($brevo_api_key) {
    $ch = curl_init('https://api.brevo.com/v3/contacts');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode([
            'email'         => $email,
            'listIds'       => [2],
            'updateEnabled' => true,
            'attributes'    => ['SIGNUP_SOURCE' => 'realestate.patizhi.ge'],
        ]),
        CURLOPT_HTTPHEADER => [
            'api-key: ' . $brevo_api_key,
            'Content-Type: application/json',
        ],
        CURLOPT_TIMEOUT => 10,
    ]);
    $response  = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code < 200 || $http_code >= 300) {
        error_log("[Newsletter] Brevo API error ({$http_code}): {$response}");
    }
}

header('Location: /socials?success=subscribed');
exit;