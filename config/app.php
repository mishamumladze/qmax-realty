<?php

declare(strict_types=1);

/**
 * QMAX Realty — Application Configuration
 *
 * Defines two constants exactly once:
 *
 *   BASE_URL  — the root-relative web path to the project (with trailing slash)
 *               e.g. /qmax-realty/ locally, / on production
 *
 *   SITE_URL  — the canonical absolute origin, always the production domain.
 *               Used wherever an absolute URL is required regardless of
 *               the current environment (JSON-LD schema, og:image, etc.)
 *               e.g. https://realestate.patizhi.ge
 *
 * How BASE_URL auto-detection works:
 *
 *   Local XAMPP:
 *     DOCUMENT_ROOT = C:/xampp/htdocs
 *     __DIR__       = C:/xampp/htdocs/qmax-realty/config
 *     BASE_URL      = /qmax-realty/
 *
 *   Production (domain root):
 *     DOCUMENT_ROOT = /var/www/html
 *     __DIR__       = /var/www/html/config
 *     BASE_URL      = /
 *
 * Zero code changes needed on deploy. It just works in both environments.
 */

if (!defined('BASE_URL')) {
    // Normalise directory separators for Windows (XAMPP uses backslashes)
    $docRoot    = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']), '/');
    $projectDir = rtrim(str_replace('\\', '/', dirname(__DIR__)), '/'); // parent of /config/

    // Subtract the filesystem document root to get the web-relative path
    $webPath = str_replace($docRoot, '', $projectDir);
    $webPath = rtrim($webPath, '/') . '/';

    // Guarantee it always starts with /
    if (!str_starts_with($webPath, '/')) {
        $webPath = '/' . $webPath;
    }

    define('BASE_URL', $webPath);
}

if (!defined('SITE_URL')) {
    // Always the production canonical domain — never localhost, never a subfolder.
    // Schema markup, og:image, and canonical tags must use absolute URLs.
    define('SITE_URL', 'https://realestate.patizhi.ge');
}

// ── Contact details — single source of truth ─────────────────────────────────
// Update here and every page/email reflects the change automatically.
if (!defined('CONTACT_EMAIL')) {
    define('CONTACT_EMAIL',     'qmax.rea@gmail.com');
    define('CONTACT_PHONE',     '+905365597376');
    define('CONTACT_WA',        'https://wa.me/905365597376');
    define('CONTACT_TLG',       'https://t.me/QMAXreal');
    define('CONTACT_ADDRESS',   '2/11 Gia Abesadze St, Tbilisi, Georgia');
}

// ── Social media links — single source of truth ─────────────────────────────
// Update here and every page/footer reflects the change automatically.
if (!defined('SOCIAL_INSTAGRAM')) {
    define('SOCIAL_INSTAGRAM', 'https://www.instagram.com/qmax_realestate');
    define('SOCIAL_FACEBOOK',  'https://www.facebook.com/qmax.rea');
    define('SOCIAL_TIKTOK',    'https://www.tiktok.com/@qmax_realty');
    define('SOCIAL_TELEGRAM',  'https://t.me/QMAXreal');
}