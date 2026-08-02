<?php
declare(strict_types=1);
require_once __DIR__ . '/auth.php';
qmx_admin_require_auth();
require_once __DIR__ . '/../booking/includes/db.php';
require_once __DIR__ . '/includes/layout.php';

$db    = qmx_db();
$error = '';

// ── Actions ───────────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $name = trim($_POST['name'] ?? '');
        $code = strtoupper(trim($_POST['code'] ?? ''));
        if (!$name || !$code) {
            $error = 'Name and code are required.';
        } elseif (!preg_match('/^[A-Z0-9_]{2,12}$/', $code)) {
            $error = 'Code must be 2–12 uppercase letters/numbers/underscores.';
        } else {
            try {
                $db->prepare("INSERT INTO promoters (name, code) VALUES (?, ?)")
                   ->execute([$name, $code]);
            } catch (\Exception $e) {
                $error = 'Code already exists. Choose a different one.';
            }
        }
    } elseif ($action === 'toggle') {
        $id = (int)($_POST['id'] ?? 0);
        $db->prepare("UPDATE promoters SET active = NOT active WHERE id = ?")
           ->execute([$id]);
    } elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $db->prepare("DELETE FROM promoters WHERE id = ?")
           ->execute([$id]);
    }

    if (!$error) {
        header('Location: promoters.php');
        exit;
    }
}

$promoters = qmx_get_promoters();

// Stats per promoter
$stats = $db->query("
    SELECT promoter_code, COUNT(*) as bookings, COALESCE(SUM(price_gel), 0) as revenue
    FROM bookings
    WHERE promoter_code IS NOT NULL AND status = 'confirmed'
    GROUP BY promoter_code
")->fetchAll(PDO::FETCH_KEY_PAIR);

// Build full stats array
$pstats = [];
foreach ($db->query("
    SELECT promoter_code, COUNT(*) as bookings, COALESCE(SUM(price_gel), 0) as revenue
    FROM bookings WHERE promoter_code IS NOT NULL AND status = 'confirmed'
    GROUP BY promoter_code
")->fetchAll() as $row) {
    $pstats[$row['promoter_code']] = $row;
}

admin_head('Promoters');
admin_nav('promoters');
admin_page_header('Promoters', 'Track walk-in referral sources');
?>

<div class="p-8">

<?php if ($error): ?>
<div class="bg-red-50 border border-red-200 text-red-700 rounded-xl p-4 mb-6 text-sm"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

    <!-- Add promoter -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 lg:col-span-1">
        <h2 class="font-semibold text-gray-800 mb-4">Add Promoter</h2>
        <form method="POST" novalidate>
            <input type="hidden" name="action" value="add">
            <div class="mb-3">
                <label class="block text-sm font-semibold text-gray-700 mb-1">Full Name</label>
                <input type="text" name="name" placeholder="e.g. Giorgi Beridze" required
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-700 mb-1">Code</label>
                <input type="text" name="code" placeholder="e.g. GIORGI" required maxlength="12"
                       style="text-transform:uppercase"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-emerald-500">
                <p class="text-xs text-gray-400 mt-1">2–12 uppercase letters/numbers. Used on bookings.</p>
            </div>
            <button class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2.5 rounded-xl transition-colors text-sm">
                Add Promoter
            </button>
        </form>
    </div>

    <!-- Promoters list -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden lg:col-span-2">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="font-semibold text-gray-800">All Promoters</h2>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wide">
                <tr>
                    <th class="px-5 py-3 text-left">Name</th>
                    <th class="px-5 py-3 text-left">Code</th>
                    <th class="px-5 py-3 text-left">Bookings</th>
                    <th class="px-5 py-3 text-left">Revenue</th>
                    <th class="px-5 py-3 text-left">Status</th>
                    <th class="px-5 py-3 text-left">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php foreach ($promoters as $p): ?>
                <?php $s = $pstats[$p['code']] ?? ['bookings' => 0, 'revenue' => 0]; ?>
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-5 py-3 font-medium text-gray-800"><?= htmlspecialchars($p['name']) ?></td>
                    <td class="px-5 py-3 font-mono text-emerald-600 font-semibold text-xs"><?= htmlspecialchars($p['code']) ?></td>
                    <td class="px-5 py-3 text-gray-600"><?= (int)$s['bookings'] ?></td>
                    <td class="px-5 py-3 text-gray-600"><?= number_format((int)$s['revenue']) ?>₾</td>
                    <td class="px-5 py-3">
                        <?= $p['active'] ? admin_badge('Active', 'green') : admin_badge('Inactive', 'gray') ?>
                    </td>
                    <td class="px-5 py-3">
                        <div class="flex gap-2">
                            <form method="POST">
                                <input type="hidden" name="action" value="toggle">
                                <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                                <button class="text-xs border border-gray-300 text-gray-600 hover:bg-gray-100 px-2.5 py-1 rounded-lg transition-colors">
                                    <?= $p['active'] ? 'Deactivate' : 'Activate' ?>
                                </button>
                            </form>
                            <form method="POST" onsubmit="return confirm('Delete <?= htmlspecialchars(addslashes($p['name'])) ?>?')">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                                <button class="text-xs border border-red-200 text-red-600 hover:bg-red-50 px-2.5 py-1 rounded-lg transition-colors">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($promoters)): ?>
                <tr><td colspan="6" class="px-5 py-8 text-center text-gray-400">No promoters yet. Add one to start tracking referrals.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>
</div>

<?php admin_foot(); ?>
