<?php
declare(strict_types=1);

/**
 * QMAX Realty — Exchange Rate Helper
 *
 * Fetches the live GEL → USD rate from frankfurter.app (free, no API key).
 * Result is cached in booking/data/exchange_rate.json for 6 hours so we
 * never hit the external API on every booking request.
 *
 * Falls back to the GEL_TO_USD constant (booking/config/booking.php) if:
 *   - The API is unreachable
 *   - The response is malformed
 *   - file_put_contents fails (permissions issue)
 */

define('EXCHANGE_CACHE_FILE', __DIR__ . '/../data/exchange_rate.json');
define('EXCHANGE_CACHE_TTL',  6 * 3600); // 6 hours in seconds
define('EXCHANGE_FALLBACK',   defined('GEL_TO_USD') ? (float)GEL_TO_USD : 0.37);

/**
 * Returns the current GEL → USD exchange rate.
 * Uses a local cache; refreshes from frankfurter.app when stale.
 */
function qmx_gel_to_usd(): float
{
    // ── 1. Try the cache ──────────────────────────────────────────────────────
    if (file_exists(EXCHANGE_CACHE_FILE)) {
        $raw = file_get_contents(EXCHANGE_CACHE_FILE);
        if ($raw !== false) {
            $cached = json_decode($raw, true);
            if (
                isset($cached['rate'], $cached['fetched_at']) &&
                is_float($cached['rate']) &&
                (time() - (int)$cached['fetched_at']) < EXCHANGE_CACHE_TTL
            ) {
                return $cached['rate'];
            }
        }
    }

    // ── 2. Fetch from frankfurter.app ─────────────────────────────────────────
    // Endpoint: GET https://api.frankfurter.app/latest?from=GEL&to=USD
    // Response: {"rates":{"USD":0.3651}, ...}
    $rate = qmx_fetch_exchange_rate();

    if ($rate !== null) {
        // Write to cache — non-fatal if it fails
        @file_put_contents(
            EXCHANGE_CACHE_FILE,
            json_encode(['rate' => $rate, 'fetched_at' => time()]),
            LOCK_EX
        );
        return $rate;
    }

    // ── 3. Fallback to hardcoded constant ─────────────────────────────────────
    error_log('[QMX Exchange] API unavailable — using fallback rate ' . EXCHANGE_FALLBACK);
    return EXCHANGE_FALLBACK;
}

/**
 * Makes the HTTP request to frankfurter.app.
 * Returns the GEL→USD rate as a float, or null on any failure.
 */
function qmx_fetch_exchange_rate(): ?float
{
    $url = 'https://api.frankfurter.app/latest?from=GEL&to=USD';

    // Use cURL if available, otherwise fall back to file_get_contents
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 5,      // 5 second timeout
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_USERAGENT      => 'QMAX Realty/1.0',
        ]);
        $body = curl_exec($ch);
        $err  = curl_error($ch);
        curl_close($ch);

        if ($err || $body === false) {
            error_log('[QMX Exchange] cURL error: ' . $err);
            return null;
        }
    } else {
        $ctx  = stream_context_create(['http' => ['timeout' => 5]]);
        $body = @file_get_contents($url, false, $ctx);
        if ($body === false) {
            error_log('[QMX Exchange] file_get_contents failed for ' . $url);
            return null;
        }
    }

    $data = json_decode($body, true);

    if (!isset($data['rates']['USD']) || !is_numeric($data['rates']['USD'])) {
        error_log('[QMX Exchange] Unexpected API response: ' . $body);
        return null;
    }

    return (float)$data['rates']['USD'];
}