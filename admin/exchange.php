<?php
declare(strict_types=1);
require_once __DIR__ . '/auth.php';
qmx_admin_require_auth();
require_once __DIR__ . '/../booking/includes/exchange.php';
require_once __DIR__ . '/includes/layout.php';

$cache_file = EXCHANGE_CACHE_FILE;
$message    = '';

// Force refresh
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    @unlink($cache_file);
    $rate = qmx_gel_to_usd();
    $message = "Refreshed. Current rate: 1 GEL = \${$rate} USD";
}

// Read cache info
$cache_info = null;
if (file_exists($cache_file)) {
    $raw = @file_get_contents($cache_file);
    if ($raw) {
        $cached = json_decode($raw, true);
        if ($cached) {
            $age_mins = round((time() - $cached['fetched_at']) / 60);
            $cache_info = [
                'rate'       => $cached['rate'],
                'fetched_at' => date('Y-m-d H:i:s', $cached['fetched_at']),
                'age_mins'   => $age_mins,
                'expires_in' => max(0, round((EXCHANGE_CACHE_TTL - (time() - $cached['fetched_at'])) / 60)),
            ];
        }
    }
}

$current_rate = qmx_gel_to_usd();

admin_head('Exchange Rate');
admin_nav('exchange');
admin_page_header('Exchange Rate', 'GEL → USD · Powered by frankfurter.app');
?>

<div class="p-8 max-w-lg">

<?php if ($message): ?>
<div class="bg-green-50 border border-green-200 text-green-700 rounded-xl p-4 mb-6 text-sm font-semibold"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
    <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold mb-1">Current Rate</p>
    <p class="text-4xl font-bold text-gray-800">1 ₾ = $<?= $current_rate ?></p>
    <p class="text-sm text-gray-500 mt-1">Fallback constant: $<?= EXCHANGE_FALLBACK ?></p>
</div>

<?php if ($cache_info): ?>
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6 text-sm">
    <p class="font-semibold text-gray-700 mb-3">Cache Status</p>
    <div class="space-y-1.5 text-gray-600">
        <p>Fetched at: <strong><?= $cache_info['fetched_at'] ?></strong></p>
        <p>Age: <strong><?= $cache_info['age_mins'] ?> minute(s)</strong></p>
        <p>Expires in: <strong><?= $cache_info['expires_in'] ?> minute(s)</strong></p>
        <p>Cache TTL: <strong><?= EXCHANGE_CACHE_TTL / 3600 ?> hours</strong></p>
    </div>
</div>
<?php else: ?>
<div class="bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl p-4 mb-6 text-sm">
    No cache file found — using fallback rate.
</div>
<?php endif; ?>

<form method="POST">
    <button class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 rounded-xl transition-colors">
        ↺ Force Refresh from frankfurter.app
    </button>
</form>

</div>

<?php admin_foot(); ?>
