<?php
declare(strict_types=1);
require_once __DIR__ . '/auth.php';
qmx_admin_require_auth();
require_once __DIR__ . '/../booking/includes/db.php';
require_once __DIR__ . '/../booking/includes/exchange.php';
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/includes/layout.php';

$all_properties  = require __DIR__ . '/../config/properties.php';
$promoters  = qmx_get_promoters(true);
$gel_to_usd = qmx_gel_to_usd();
$error      = '';
$success    = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $slug         = trim($_POST['property_slug']      ?? '');
    $first_name   = trim($_POST['first_name']     ?? '');
    $last_name    = trim($_POST['last_name']      ?? '');
    $email        = trim($_POST['email']          ?? '');
    $phone        = trim($_POST['phone']          ?? '');
    $language     = trim($_POST['language']       ?? '');
    $property_date    = trim($_POST['property_date']       ?? '');
    $pax          = (int)($_POST['pax']           ?? 1);
    $payment_type = trim($_POST['payment_type']   ?? 'cash');
    $pay_status   = trim($_POST['payment_status'] ?? 'unpaid');
    $deposit      = (int)($_POST['deposit_amount']?? 0);
    $promoter     = trim($_POST['promoter_code']  ?? '');
    $notes        = trim($_POST['notes']          ?? '');
    $custom_price = (int)($_POST['custom_price']  ?? 0);

    $tour = $all_properties[$slug] ?? null;

    if (!$tour)          $error = 'Please select a tour.';
    elseif (!$first_name || !$last_name) $error = 'First and last name are required.';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $error = 'Valid email is required.';
    elseif (!$phone)     $error = 'Phone is required.';
    elseif (!$property_date) $error = 'Tour date is required.';
    elseif (!in_array($language, ['English', 'Russian'], true)) $error = 'Select a guide language.';
    else {
        $price_gel = $custom_price > 0 ? $custom_price : (int)($tour['price'] ?? $tour['price_sedan'] ?? 0);
        $price_gel = $price_gel * $pax;
        $price_usd = round($price_gel * $gel_to_usd, 2);

        // Validate deposit
        $min_deposit = (int)ceil($price_gel * 0.5);
        if ($pay_status === 'deposit' && $deposit < $min_deposit) {
            $error = "Minimum deposit is {$min_deposit}₾ (50% of {$price_gel}₾).";
        } else {
            $ref = qmx_create_booking([
                'property_slug'      => $slug,
                'property_title'     => $tour['title'],
                'property_type'      => $tour['type'],
                'property_date'      => $property_date,
                'start_time'     => $tour['start_time'],
                'pax'            => $pax,
                'price_gel'      => $price_gel,
                'price_usd'      => $price_usd,
                'first_name'     => $first_name,
                'last_name'      => $last_name,
                'email'          => $email,
                'phone'          => $phone,
                'language'       => $language,
                'notes'          => $notes,
                'payment_type'   => $payment_type,
                'payment_status' => $pay_status,
                'deposit_amount' => $pay_status === 'deposit' ? $deposit : ($pay_status === 'paid' ? $price_gel : 0),
                'created_by'     => 'admin',
                'promoter_code'  => $promoter ?: null,
            ]);

            // Auto-confirm walk-in bookings
            qmx_db()->prepare("UPDATE bookings SET status = 'confirmed' WHERE reference = ?")
                    ->execute([$ref]);

            $success = $ref;
        }
    }
}

admin_head('New Walk-in Booking');
admin_nav('new-booking');
admin_page_header('New Walk-in Booking', 'Manual booking for cash/card customers');
?>

<div class="p-8 max-w-2xl">

<?php if ($success): ?>
<div class="bg-green-50 border border-green-200 rounded-xl p-5 mb-6">
    <p class="font-bold text-green-800 text-lg">✓ Booking Created!</p>
    <p class="text-green-700 text-sm mt-1">Reference: <strong class="font-mono"><?= htmlspecialchars($success) ?></strong></p>
    <div class="flex gap-3 mt-3">
        <a href="bookings.php?ref=<?= urlencode($success) ?>" class="text-sm bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-semibold transition-colors">View Booking</a>
        <a href="new-booking.php" class="text-sm border border-green-300 text-green-700 hover:bg-green-100 px-4 py-2 rounded-lg font-semibold transition-colors">New Booking</a>
    </div>
</div>
<?php endif; ?>

<?php if ($error): ?>
<div class="bg-red-50 border border-red-200 text-red-700 rounded-xl p-4 mb-6 text-sm"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
    <form method="POST" novalidate>

        <!-- Tour -->
        <div class="mb-5">
            <label class="block text-sm font-semibold text-gray-700 mb-1">Tour *</label>
            <select name="property_slug" required
                    class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                    onchange="updatePrice(this)">
                <option value="">Select a tour…</option>
                <?php foreach ($all_properties as $slug => $t): ?>
                <option value="<?= htmlspecialchars($slug) ?>"
                        data-price="<?= (int)($t['price'] ?? $t['price_sedan'] ?? 0) ?>"
                        data-start="<?= htmlspecialchars($t['start_time']) ?>"
                        <?= (($_POST['property_slug'] ?? '') === $slug) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($t['title']) ?>
                    (<?= isset($t['price']) ? (int)$t['price'] . '₾/person' : 'from ' . (int)($t['price_sedan'] ?? 0) . '₾' ?>)
                </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Customer -->
        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">First Name *</label>
                <input type="text" name="first_name" value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>" required
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Last Name *</label>
                <input type="text" name="last_name" value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>" required
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Email *</label>
                <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Phone *</label>
                <input type="tel" name="phone" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" required
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Guide Language *</label>
                <select name="language" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    <option value="">Select…</option>
                    <option value="English" <?= (($_POST['language'] ?? '') === 'English') ? 'selected' : '' ?>>🇬🇧 English</option>
                    <option value="Russian" <?= (($_POST['language'] ?? '') === 'Russian') ? 'selected' : '' ?>>🇷🇺 Russian</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Tour Date *</label>
                <input type="date" name="property_date" value="<?= htmlspecialchars($_POST['property_date'] ?? '') ?>" required
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Passengers *</label>
                <select name="pax" required onchange="recalcPrice()"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    <?php for ($i = 1; $i <= 20; $i++): ?>
                    <option value="<?= $i ?>" <?= (int)($_POST['pax'] ?? 1) === $i ? 'selected' : '' ?>><?= $i ?> <?= $i === 1 ? 'person' : 'people' ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">
                    Custom Price per Person (GEL)
                    <span class="text-gray-400 font-normal">— leave 0 for default</span>
                </label>
                <input type="number" name="custom_price" value="<?= (int)($_POST['custom_price'] ?? 0) ?>"
                       min="0" onchange="recalcPrice()"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </div>
        </div>

        <!-- Price preview -->
        <div id="price-preview" class="bg-emerald-50 border border-emerald-200 rounded-lg px-4 py-3 mb-5 text-sm hidden">
            <div class="flex justify-between font-semibold text-gray-800">
                <span id="price-label">Total</span>
                <span id="price-value"></span>
            </div>
        </div>

        <!-- Payment -->
        <div class="border-t border-gray-100 pt-5 mb-4">
            <p class="text-sm font-semibold text-gray-700 mb-3">Payment</p>
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Payment Method</label>
                    <select name="payment_type" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <option value="cash" <?= (($_POST['payment_type'] ?? 'cash') === 'cash')  ? 'selected' : '' ?>>Cash</option>
                        <option value="card" <?= (($_POST['payment_type'] ?? '') === 'card')       ? 'selected' : '' ?>>Card</option>
                        <option value="online" <?= (($_POST['payment_type'] ?? '') === 'online')   ? 'selected' : '' ?>>Online (PayPal)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Payment Status</label>
                    <select name="payment_status" id="payment_status_sel" onchange="toggleDeposit(this.value)"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <option value="unpaid"  <?= (($_POST['payment_status'] ?? 'unpaid') === 'unpaid')  ? 'selected' : '' ?>>Unpaid</option>
                        <option value="deposit" <?= (($_POST['payment_status'] ?? '') === 'deposit')       ? 'selected' : '' ?>>Deposit Paid</option>
                        <option value="paid"    <?= (($_POST['payment_status'] ?? '') === 'paid')          ? 'selected' : '' ?>>Paid in Full</option>
                    </select>
                </div>
            </div>

            <div id="deposit-row" class="mb-4 hidden">
                <label class="block text-xs font-semibold text-gray-600 mb-1">Deposit Amount (GEL) — min 50%</label>
                <input type="number" name="deposit_amount" id="deposit_amount"
                       value="<?= (int)($_POST['deposit_amount'] ?? 0) ?>" min="0"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                <p id="deposit-hint" class="text-xs text-gray-400 mt-1"></p>
            </div>
        </div>

        <!-- Promoter -->
        <?php if (!empty($promoters)): ?>
        <div class="mb-4">
            <label class="block text-sm font-semibold text-gray-700 mb-1">Promoter / Referral</label>
            <select name="promoter_code" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                <option value="">None / Direct</option>
                <?php foreach ($promoters as $p): ?>
                <option value="<?= htmlspecialchars($p['code']) ?>"
                        <?= (($_POST['promoter_code'] ?? '') === $p['code']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($p['name']) ?> (<?= htmlspecialchars($p['code']) ?>)
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php endif; ?>

        <!-- Notes -->
        <div class="mb-6">
            <label class="block text-sm font-semibold text-gray-700 mb-1">Notes</label>
            <textarea name="notes" rows="2" placeholder="Special requests, cash amount paid, etc."
                      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 resize-none"><?= htmlspecialchars($_POST['notes'] ?? '') ?></textarea>
        </div>

        <button type="submit"
                class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 px-6 rounded-xl transition-colors duration-200">
            Create Booking
        </button>
    </form>
</div>

</div>

<script>
const GEL_TO_USD = <?= $gel_to_usd ?>;
let basePrice = 0;

function updatePrice(sel) {
    const opt = sel.options[sel.selectedIndex];
    basePrice = parseInt(opt.dataset.price || 0, 10);
    recalcPrice();
}

function recalcPrice() {
    const pax         = parseInt(document.querySelector('[name=pax]').value, 10) || 1;
    const custom      = parseInt(document.querySelector('[name=custom_price]').value, 10) || 0;
    const pricePerPax = custom > 0 ? custom : basePrice;
    const total       = pricePerPax * pax;
    const usd         = (total * GEL_TO_USD).toFixed(2);

    const preview = document.getElementById('price-preview');
    if (total > 0) {
        document.getElementById('price-label').textContent = pax + ' × ' + pricePerPax + '₾';
        document.getElementById('price-value').textContent = total + '₾  (~$' + usd + ')';
        preview.classList.remove('hidden');
        // Update deposit hint
        const minDep = Math.ceil(total * 0.5);
        document.getElementById('deposit-hint').textContent = 'Minimum deposit: ' + minDep + '₾';
        document.getElementById('deposit_amount').min = minDep;
    } else {
        preview.classList.add('hidden');
    }
}

function toggleDeposit(val) {
    document.getElementById('deposit-row').classList.toggle('hidden', val !== 'deposit');
}

// Init
toggleDeposit(document.getElementById('payment_status_sel').value);
</script>

<?php admin_foot(); ?>
