<?php
declare(strict_types=1);

/**
 * QMAX Realty — Admin Auth Guard
 *
 * require_once this at the top of every admin page.
 * Handles session start, login check, and brute-force protection.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load secrets for ADMIN_PASSWORD constant
$secrets = dirname(__DIR__) . '/booking/config/secrets.php';
if (file_exists($secrets)) require_once $secrets;
if (!defined('ADMIN_PASSWORD')) define('ADMIN_PASSWORD', '');

define('ADMIN_SESSION_TTL',   7200);  // 2 hours inactivity timeout
define('ADMIN_MAX_ATTEMPTS',  5);     // lockout after 5 failures
define('ADMIN_LOCKOUT_SECS',  900);   // 15 minute lockout

/**
 * Returns true if admin is currently logged in with a valid session.
 */
function qmx_admin_logged_in(): bool
{
    if (empty($_SESSION['admin_logged_in'])) return false;
    if (empty($_SESSION['admin_last_active'])) return false;

    // Timeout check
    if ((time() - $_SESSION['admin_last_active']) > ADMIN_SESSION_TTL) {
        session_unset();
        return false;
    }

    $_SESSION['admin_last_active'] = time();
    return true;
}

/**
 * Check brute-force state for this IP.
 * Returns ['locked' => bool, 'attempts' => int, 'remaining' => int]
 */
function qmx_admin_brute_state(): array
{
    $ip   = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $file = sys_get_temp_dir() . '/' . hash('sha256', 'qmx_admin_bf_' . $ip) . '.tmp';

    if (!file_exists($file)) {
        return ['locked' => false, 'attempts' => 0, 'remaining' => 0];
    }

    $data = json_decode(@file_get_contents($file), true);
    if (!$data) return ['locked' => false, 'attempts' => 0, 'remaining' => 0];

    $locked    = isset($data['locked_at']) && (time() - $data['locked_at']) < ADMIN_LOCKOUT_SECS;
    $remaining = $locked ? (ADMIN_LOCKOUT_SECS - (time() - $data['locked_at'])) : 0;

    return [
        'locked'    => $locked,
        'attempts'  => (int)($data['attempts'] ?? 0),
        'remaining' => $remaining,
    ];
}

/**
 * Record a failed login attempt. Locks out after ADMIN_MAX_ATTEMPTS.
 */
function qmx_admin_record_failure(): void
{
    $ip   = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $file = sys_get_temp_dir() . '/' . hash('sha256', 'qmx_admin_bf_' . $ip) . '.tmp';

    $data = ['attempts' => 1];
    if (file_exists($file)) {
        $existing = json_decode(@file_get_contents($file), true);
        if ($existing) {
            $data['attempts'] = ($existing['attempts'] ?? 0) + 1;
        }
    }

    if ($data['attempts'] >= ADMIN_MAX_ATTEMPTS) {
        $data['locked_at'] = time();
    }

    @file_put_contents($file, json_encode($data), LOCK_EX);
}

/**
 * Clear brute-force record on successful login.
 */
function qmx_admin_clear_failures(): void
{
    $ip   = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $file = sys_get_temp_dir() . '/' . hash('sha256', 'qmx_admin_bf_' . $ip) . '.tmp';
    @unlink($file);
}

/**
 * Redirect to login if not authenticated.
 */
function qmx_admin_require_auth(): void
{
    if (!qmx_admin_logged_in()) {
        header('Location: ' . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') . '/../admin/login.php');
        exit;
    }
}
