<?php
declare(strict_types=1);
require_once __DIR__ . '/auth.php';
qmx_admin_require_auth();
require_once __DIR__ . '/../booking/includes/db.php';
require_once __DIR__ . '/includes/layout.php';

$db = qmx_db();

// ── Actions ───────────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $ref    = trim($_POST['reference'] ?? '');

    if ($ref && $action === 'confirm') {
        $db->prepare("UPDATE bookings SET status = 'confirmed', payment_status = 'paid' WHERE reference = ?")
           ->execute([$ref]);
    } elseif ($ref && $action === 'cancel') {
        $db->prepare("UPDATE bookings SET status = 'cancelled' WHERE reference = ?")
           ->execute([$ref]);
    } elseif ($ref && $action === 'update_payment') {
        $ps      = $_POST['payment_status'] ?? 'unpaid';
        $deposit = (int)($_POST['deposit_amount'] ?? 0);
        $db->prepare("UPDATE bookings SET payment_status = ?, deposit_amount = ? WHERE reference = ?")
           ->execute([$ps, $deposit, $ref]);
    }

    header('Location: bookings.php' . ($_GET ? '?' . http_build_query($_GET) : ''));
    exit;
}

// ── CSV Export ────────────────────────────────────────────────────────────────
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="bookings-' . date('Y-m-d') . '.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Reference','First Name','Last Name','Email','Phone','Tour','Date','Pax','Price GEL','Price USD','Language','Status','Payment','Deposit','Type','Promoter','Created By','Created At','Notes']);
    $all = $db->query("SELECT * FROM bookings ORDER BY created_at DESC")->fetchAll();
    foreach ($all as $b) {
        fputcsv($out, [
            $b['reference'], $b['first_name'], $b['last_name'], $b['email'], $b['phone'],
            $b['property_title'], $b['property_date'], $b['pax'], $b['price_gel'], $b['price_usd'],
            $b['language'], $b['status'], $b['payment_status'], $b['deposit_amount'],
            $b['payment_type'], $b['promoter_code'] ?? '', $b['created_by'], $b['created_at'], $b['notes'],
        ]);
    }
    fclose($out);
    exit;
}

// ── Filters ───────────────────────────────────────────────────────────────────
$search   = trim($_GET['q']       ?? '');
$status   = trim($_GET['status']  ?? '');
$tour     = trim($_GET['tour']    ?? '');
$date_from= trim($_GET['from']    ?? '');
$date_to  = trim($_GET['to']      ?? '');
$ref_focus= trim($_GET['ref']     ?? '');

$where  = ['1=1'];
$params = [];

if ($search) {
    $where[]  = "(reference LIKE ? OR first_name LIKE ? OR last_name LIKE ? OR email LIKE ?)";
    $s = "%{$search}%";
    $params = array_merge($params, [$s, $s, $s, $s]);
}
if ($status)    { $where[] = "status = ?";     $params[] = $status; }
if ($tour)      { $where[] = "property_slug = ?";  $params[] = $tour; }
if ($date_from) { $where[] = "property_date >= ?"; $params[] = $date_from; }
if ($date_to)   { $where[] = "property_date <= ?"; $params[] = $date_to; }

$sql = "SELECT * FROM bookings WHERE " . implode(' AND ', $where) . " ORDER BY created_at DESC";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$bookings = $stmt->fetchAll();

// Tour list for filter dropdown
$tours = $db->query("SELECT DISTINCT property_slug, property_title FROM bookings ORDER BY property_title")->fetchAll();

// Detail view
$detail = null;
if ($ref_focus) {
    $detail = qmx_get_booking($ref_focus);
}

admin_head('Bookings');
admin_nav('bookings');
admin_page_header('Bookings', count($bookings) . ' result(s)');
?>

<div class="p-8">

<?php if ($detail): ?>
<!-- ── Detail Panel ─────────────────────────────────────────────────────────── -->
<div class="bg-white rounded-xl shadow-sm border border-gray-100 mb-8 overflow-hidden">
    <div class="bg-emerald-600 px-6 py-4 flex items-center justify-between">
        <div>
            <p class="text-emerald-200 text-xs font-semibold uppercase tracking-wider">Booking Detail</p>
            <p class="text-white text-xl font-bold font-mono"><?= htmlspecialchars($detail['reference']) ?></p>
        </div>
        <a href="bookings.php" class="text-emerald-200 hover:text-white text-sm">← Back</a>
    </div>
    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Customer</h3>
            <p class="text-gray-800 font-semibold"><?= htmlspecialchars($detail['first_name'] . ' ' . $detail['last_name']) ?></p>
            <p class="text-gray-600 text-sm"><?= htmlspecialchars($detail['email']) ?></p>
            <p class="text-gray-600 text-sm"><?= htmlspecialchars($detail['phone']) ?></p>
            <p class="text-gray-600 text-sm mt-1"><?= $detail['language'] === 'Russian' ? '🇷🇺 Russian' : '🇬🇧 English' ?></p>
        </div>
        <div>
            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Tour</h3>
            <p class="text-gray-800 font-semibold"><?= htmlspecialchars($detail['property_title']) ?></p>
            <p class="text-gray-600 text-sm">📅 <?= htmlspecialchars($detail['property_date']) ?> · <?= htmlspecialchars($detail['start_time']) ?></p>
            <p class="text-gray-600 text-sm">👥 <?= (int)$detail['pax'] ?> passenger(s)</p>
        </div>
        <div>
            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Payment</h3>
            <p class="text-gray-800 font-semibold text-lg"><?= (int)$detail['price_gel'] ?>₾ <span class="text-gray-400 text-sm font-normal">(~$<?= number_format((float)$detail['price_usd'], 2) ?>)</span></p>
            <p class="text-gray-600 text-sm">Type: <?= htmlspecialchars(ucfirst($detail['payment_type'])) ?></p>
            <?php if ($detail['deposit_amount'] > 0): ?>
            <p class="text-gray-600 text-sm">Deposit: <?= (int)$detail['deposit_amount'] ?>₾ · Remaining: <?= max(0, (int)$detail['price_gel'] - (int)$detail['deposit_amount']) ?>₾</p>
            <?php endif; ?>
            <?php if ($detail['promoter_code']): ?>
            <p class="text-gray-600 text-sm">🏷️ Promoter: <strong><?= htmlspecialchars($detail['promoter_code']) ?></strong></p>
            <?php endif; ?>
            <?php if ($detail['notes']): ?>
            <p class="text-gray-600 text-sm mt-2">📝 <?= htmlspecialchars($detail['notes']) ?></p>
            <?php endif; ?>
        </div>
        <div>
            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Actions</h3>
            <div class="flex flex-wrap gap-2 mb-4">
                <?php if ($detail['status'] !== 'confirmed'): ?>
                <form method="POST">
                    <input type="hidden" name="reference" value="<?= htmlspecialchars($detail['reference']) ?>">
                    <input type="hidden" name="action" value="confirm">
                    <button class="bg-green-600 hover:bg-green-700 text-white text-sm font-semibold px-4 py-2 rounded-lg transition-colors">✓ Confirm</button>
                </form>
                <?php endif; ?>
                <?php if ($detail['status'] !== 'cancelled'): ?>
                <form method="POST" onsubmit="return confirm('Cancel this booking?')">
                    <input type="hidden" name="reference" value="<?= htmlspecialchars($detail['reference']) ?>">
                    <input type="hidden" name="action" value="cancel">
                    <button class="bg-red-600 hover:bg-red-700 text-white text-sm font-semibold px-4 py-2 rounded-lg transition-colors">✗ Cancel</button>
                </form>
                <?php endif; ?>
            </div>
            <!-- Update payment status -->
            <form method="POST" class="space-y-2">
                <input type="hidden" name="reference" value="<?= htmlspecialchars($detail['reference']) ?>">
                <input type="hidden" name="action" value="update_payment">
                <select name="payment_status" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    <option value="unpaid"  <?= $detail['payment_status'] === 'unpaid'  ? 'selected' : '' ?>>Unpaid</option>
                    <option value="deposit" <?= $detail['payment_status'] === 'deposit' ? 'selected' : '' ?>>Deposit Paid</option>
                    <option value="paid"    <?= $detail['payment_status'] === 'paid'    ? 'selected' : '' ?>>Paid in Full</option>
                </select>
                <input type="number" name="deposit_amount" placeholder="Deposit amount (GEL)"
                       value="<?= (int)$detail['deposit_amount'] ?>"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                <button class="w-full bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold py-2 rounded-lg transition-colors">Update Payment</button>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- ── Filters ─────────────────────────────────────────────────────────────── -->
<form method="GET" class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3">
        <input type="text" name="q" value="<?= htmlspecialchars($search) ?>"
               placeholder="Search name, email, ref…"
               class="col-span-2 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-400">
        <select name="status" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
            <option value="">All statuses</option>
            <option value="pending"   <?= $status === 'pending'   ? 'selected' : '' ?>>Pending</option>
            <option value="confirmed" <?= $status === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
            <option value="cancelled" <?= $status === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
        </select>
        <select name="tour" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
            <option value="">All tours</option>
            <?php foreach ($tours as $t): ?>
            <option value="<?= htmlspecialchars($t['property_slug']) ?>" <?= $tour === $t['property_slug'] ? 'selected' : '' ?>>
                <?= htmlspecialchars(explode(':', $t['property_title'])[0]) ?>
            </option>
            <?php endforeach; ?>
        </select>
        <input type="date" name="from" value="<?= htmlspecialchars($date_from) ?>"
               class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
        <input type="date" name="to" value="<?= htmlspecialchars($date_to) ?>"
               class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
    </div>
    <div class="flex gap-2 mt-3">
        <button class="bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold px-4 py-2 rounded-lg transition-colors">Filter</button>
        <a href="bookings.php" class="border border-gray-300 text-gray-600 hover:bg-gray-50 text-sm font-medium px-4 py-2 rounded-lg transition-colors">Reset</a>
        <a href="?export=csv" class="ml-auto border border-gray-300 text-gray-600 hover:bg-gray-50 text-sm font-medium px-4 py-2 rounded-lg transition-colors">↓ Export CSV</a>
    </div>
</form>

<!-- ── Table ───────────────────────────────────────────────────────────────── -->
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wide">
                <tr>
                    <th class="px-5 py-3 text-left">Reference</th>
                    <th class="px-5 py-3 text-left">Customer</th>
                    <th class="px-5 py-3 text-left">Tour</th>
                    <th class="px-5 py-3 text-left">Tour Date</th>
                    <th class="px-5 py-3 text-left">Pax</th>
                    <th class="px-5 py-3 text-left">Amount</th>
                    <th class="px-5 py-3 text-left">Status</th>
                    <th class="px-5 py-3 text-left">Payment</th>
                    <th class="px-5 py-3 text-left">Promoter</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php foreach ($bookings as $b): ?>
                <tr class="hover:bg-emerald-50 transition-colors cursor-pointer" onclick="window.location='bookings.php?ref=<?= urlencode($b['reference']) ?>'">
                    <td class="px-5 py-3">
                        <span class="font-mono text-emerald-600 font-semibold text-xs"><?= htmlspecialchars($b['reference']) ?></span>
                        <?php if ($b['created_by'] === 'admin'): ?>
                        <span class="ml-1 text-xs bg-purple-100 text-purple-700 px-1.5 py-0.5 rounded-full">walk-in</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-5 py-3 text-gray-700">
                        <?= htmlspecialchars($b['first_name'] . ' ' . $b['last_name']) ?>
                        <span class="text-gray-400 ml-1"><?= $b['language'] === 'Russian' ? '🇷🇺' : '🇬🇧' ?></span>
                    </td>
                    <td class="px-5 py-3 text-gray-600 max-w-[160px] truncate"><?= htmlspecialchars(explode(':', $b['property_title'])[0]) ?></td>
                    <td class="px-5 py-3 text-gray-600"><?= htmlspecialchars($b['property_date']) ?></td>
                    <td class="px-5 py-3 text-gray-600"><?= (int)$b['pax'] ?></td>
                    <td class="px-5 py-3 font-semibold text-gray-800"><?= (int)$b['price_gel'] ?>₾</td>
                    <td class="px-5 py-3">
                        <?= match($b['status']) {
                            'confirmed' => admin_badge('Confirmed', 'green'),
                            'cancelled' => admin_badge('Cancelled', 'red'),
                            default     => admin_badge('Pending', 'amber'),
                        } ?>
                    </td>
                    <td class="px-5 py-3">
                        <?= match($b['payment_status']) {
                            'paid'    => admin_badge('Paid', 'green'),
                            'deposit' => admin_badge('Deposit', 'amber'),
                            default   => admin_badge('Unpaid', 'gray'),
                        } ?>
                    </td>
                    <td class="px-5 py-3 text-gray-500 text-xs"><?= htmlspecialchars($b['promoter_code'] ?? '—') ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($bookings)): ?>
                <tr><td colspan="9" class="px-5 py-10 text-center text-gray-400">No bookings found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</div>
<?php admin_foot(); ?>
