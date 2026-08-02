<?php
declare(strict_types=1);

/**
 * QMAX Realty — Booking System Configuration
 *
 * ════════════════════════════════════════════════════════════════
 * SETUP CHECKLIST
 * ════════════════════════════════════════════════════════════════
 *
 * STEP 1 — Create the email in cPanel:
 *   cPanel → Email Accounts → Create
 *   Recommended: booking@realestate.patizhi.ge
 *   Set a strong password, put it in booking/config/secrets.php
 *
 * STEP 2 — Find your SMTP host in cPanel:
 *   cPanel → Email Accounts → Connect Devices (on the new address)
 *   It will show something like: mail.realestate.patizhi.ge or realestate.patizhi.ge
 *   Copy it into SMTP_HOST below.
 *
 *   Typical cPanel settings:
 *     Port 465 + SSL  (SMTP_ENCRYPTION = 'ssl')   ← most common on cPanel
 *     Port 587 + TLS  (SMTP_ENCRYPTION = 'tls')   ← also common
 *   Try 465/ssl first. If email fails, switch to 587/tls.
 *
 * STEP 3 — PayPal Client ID:
 *   https://developer.paypal.com → Apps & Credentials → Create App
 *   Paste the Client ID into booking/config/secrets.php
 *   Test with PAYPAL_MODE = 'sandbox', switch to 'live' when ready.
 *
 * STEP 4 — Secrets file:
 *   Copy booking/config/secrets.php to the server manually.
 *   It is gitignored and must never be committed.
 *
 * STEP 5 — Protect the data/ directory:
 *   Create data/.htaccess with content:  Deny from all
 */

// ── Load secrets (gitignored — never committed) ───────────────────────────────
$secrets_file = __DIR__ . '/secrets.php';
if (!file_exists($secrets_file)) {
    error_log('[QMX] CRITICAL: booking/config/secrets.php not found. Booking system will not work.');
    // Define placeholders so the rest of the app doesn't crash with undefined constant errors
    if (!defined('SMTP_PASS'))        define('SMTP_PASS',        '');
} else {
    require_once $secrets_file;
}

// ── Email — your own domain via cPanel SMTP ───────────────────────────────────
define('SMTP_HOST',       'mail.realestate.patizhi.ge'); // ← from cPanel "Connect Devices"
define('SMTP_PORT',       465);                 // 465 for SSL, 587 for TLS/STARTTLS
define('SMTP_ENCRYPTION', 'ssl');               // 'ssl' (port 465) | 'tls' (port 587)
define('SMTP_USER',       'booking@realestate.patizhi.ge');

define('MAIL_FROM',      'booking@realestate.patizhi.ge');
define('MAIL_FROM_NAME', 'QMAX Realty');
define('MAIL_BCC',       'misha.mumladze2007@gmail.com');

// ── PayPal ────────────────────────────────────────────────────────────────────
define('PAYPAL_MODE',     'live');  // 'sandbox' | 'live'
define('PAYPAL_CURRENCY', 'USD');   // PayPal doesn't support GEL

// ── GEL → USD fallback rate ───────────────────────────────────────────────────
// Used only if the frankfurter.app live rate fetch fails.
// Update this monthly as a safety net.
define('GEL_TO_USD', 0.38);
define('GEL_TO_EUR', 0.33);
define('USD_TO_EUR', 0.87);

// ── SQLite database path ──────────────────────────────────────────────────────
// Ideally one level above public_html so it's not web-accessible.
// e.g. define('DB_PATH', '/home/YOUR_CPANEL_USER/bookings.sqlite');
// Default below works if data/ has a Deny-all .htaccess.
define('DB_PATH', __DIR__ . '/../data/bookings.sqlite');

// ── Booking rules ─────────────────────────────────────────────────────────────
define('MIN_ADVANCE_DAYS', 1);
define('MAX_ADVANCE_DAYS', 365);
define('MAX_PAX',          15);