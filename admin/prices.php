<?php
declare(strict_types=1);
require_once __DIR__ . '/auth.php';
qmx_admin_require_auth();
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/includes/layout.php';

$prices_file = __DIR__ . '/../config/prices.php';
$all_properties   = require __DIR__ . '/../config/properties.php';
$success     = false;
$error       = '';

// Load current overrides
$overrides = [];
if (file_exists($prices_file)) {
    $overrides = require $prices_file;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_prices = [];
    foreach ($all_properties as $slug => $tour) {
        $val = (int)($_POST['price_' . $slug] ?? 0);
        if ($val > 0) {
            $new_prices[$slug] = $val;
        }
    }

    $content = "<?php\ndeclare(strict_types=1);\n\n/**\n * QMAX Realty — Price Overrides\n * Managed via admin panel. Do not edit manually.\n * Last updated: " . date('Y-m-d H:i:s') . "\n */\n\nreturn " . var_export($new_prices, true) . ";\n";

    if (file_put_contents($prices_file, $content) !== false) {
        $overrides = $new_prices;
        $success = true;
    } else {
        $error = 'Could not write prices file. Check file permissions on config/prices.php.';
    }
}

admin_head('Prices');
admin_nav('prices');
admin_page_header('Tour Prices', 'Override default prices from config/properties.php');
?>

<div class="p-8 max-w-2xl">

<?php if ($success): ?>
<div class="bg-green-50 border border-green-200 text-green-700 rounded-xl p-4 mb-6 text-sm font-semibold">✓ Prices saved successfully.</div>
<?php endif; ?>
<?php if ($error): ?>
<div class="bg-red-50 border border-red-200 text-red-700 rounded-xl p-4 mb-6 text-sm"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4 mb-6 text-sm text-emerald-800">
    <strong>How this works:</strong> Setting a price here overrides the default in <code>config/properties.php</code>.
    Leave a field at 0 to use the default price. Changes take effect immediately.
</div>

<form method="POST">
    <div class="space-y-3">
        <?php foreach ($all_properties as $slug => $tour):
            $default  = (int)($tour['price'] ?? $tour['price_sedan'] ?? 0);
            $override = (int)($overrides[$slug] ?? 0);
            $current  = $override > 0 ? $override : $default;
            $is_private = $tour['type'] !== 'group';
        ?>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 flex items-center gap-4">
            <div class="flex-1 min-w-0">
                <p class="font-semibold text-gray-800 text-sm truncate"><?= htmlspecialchars($tour['title']) ?></p>
                <div class="flex items-center gap-2 mt-0.5">
                    <span class="text-xs text-gray-400">Default: <?= $default ?>₾<?= $is_private ? '/vehicle' : '/person' ?></span>
                    <?php if ($override > 0): ?>
                    <span class="text-xs text-emerald-600 font-semibold">→ Override: <?= $override ?>₾</span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="flex items-center gap-2 flex-shrink-0">
                <span class="text-sm text-gray-500">₾</span>
                <input type="number" name="price_<?= htmlspecialchars($slug) ?>"
                       value="<?= $override ?>"
                       min="0" placeholder="<?= $default ?>"
                       class="w-24 border border-gray-300 rounded-lg px-3 py-2 text-sm text-center focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <button class="mt-6 w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 rounded-xl transition-colors">
        Save Prices
    </button>
</form>

</div>

<?php admin_foot(); ?>
