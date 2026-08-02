<?php
declare(strict_types=1);
require_once __DIR__ . '/auth.php';
qmx_admin_require_auth();
require_once __DIR__ . '/../booking/includes/db.php';
require_once __DIR__ . '/includes/layout.php';

$db = qmx_db();

// ── Stats ─────────────────────────────────────────────────────────────────────
$month_start = date('Y-m-01');
$month_end   = date('Y-m-t');

$stats = $db->query("
    SELECT
        COUNT(*) as total_bookings,
        COALESCE(SUM(price_gel), 0) as total_gel,
        COALESCE(SUM(price_usd), 0) as total_usd,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed,
        SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled,
        SUM(CASE WHEN language = 'English' THEN 1 ELSE 0 END) as english,
        SUM(CASE WHEN language = 'Russian' THEN 1 ELSE 0 END) as russian
    FROM bookings
    WHERE property_date BETWEEN '{$month_start}' AND '{$month_end}'
")->fetch();

$top_tour = $db->query("
    SELECT property_title, COUNT(*) as cnt
    FROM bookings
    WHERE property_date BETWEEN '{$month_start}' AND '{$month_end}'
    GROUP BY property_slug
    ORDER BY cnt DESC
    LIMIT 1
")->fetch();

$total_all_time = $db->query("SELECT COUNT(*) FROM bookings WHERE status = 'confirmed'")->fetchColumn();

// ── Recent bookings ───────────────────────────────────────────────────────────
$recent = $db->query("
    SELECT * FROM bookings
    ORDER BY created_at DESC
    LIMIT 15
")->fetchAll();

admin_head('Dashboard');
admin_nav('index');
admin_page_header('Dashboard', date('F Y') . ' overview');
?>

<div class="p-8">

    <!-- Stat cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
            <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">Bookings This Month</p>
            <p class="text-3xl font-bold text-gray-800 mt-1"><?= (int)$stats['total_bookings'] ?></p>
            <p class="text-xs text-gray-400 mt-1"><?= $total_all_time ?> all time</p>
        </div>
        <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
            <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">Revenue This Month</p>
            <p class="text-3xl font-bold text-gray-800 mt-1"><?= number_format((int)$stats['total_gel']) ?>₾</p>
            <p class="text-xs text-gray-400 mt-1">~$<?= number_format((float)$stats['total_usd'], 0) ?> USD</p>
        </div>
        <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
            <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">Status</p>
            <div class="flex items-center gap-2 mt-2 flex-wrap">
                <?= admin_badge((int)$stats['confirmed'] . ' confirmed', 'green') ?>
                <?= admin_badge((int)$stats['pending'] . ' pending', 'amber') ?>
                <?= admin_badge((int)$stats['cancelled'] . ' cancelled', 'red') ?>
            </div>
        </div>
        <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
            <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">Languages</p>
            <div class="flex items-center gap-2 mt-2 flex-wrap">
                <?= admin_badge('🇬🇧 ' . (int)$stats['english'] . ' ENG', 'blue') ?>
                <?= admin_badge('🇷🇺 ' . (int)$stats['russian'] . ' RUS', 'purple') ?>
            </div>
            <?php if ($top_tour): ?>
            <p class="text-xs text-gray-400 mt-2">Top: <?= htmlspecialchars(explode(':', $top_tour['property_title'])[0]) ?> (<?= $top_tour['cnt'] ?>)</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Recent bookings -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="font-semibold text-gray-800">Recent Bookings</h2>
            <a href="bookings.php" class="text-emerald-600 hover:text-emerald-700 text-sm font-medium">View all →</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wide">
                    <tr>
                        <th class="px-6 py-3 text-left">Reference</th>
                        <th class="px-6 py-3 text-left">Customer</th>
                        <th class="px-6 py-3 text-left">Tour</th>
                        <th class="px-6 py-3 text-left">Date</th>
                        <!-- <th class="px-6 py-3 text-left">Pax</th> -->
                        <th class="px-6 py-3 text-left">Amount</th>
                        <th class="px-6 py-3 text-left">Status</th>
                        <th class="px-6 py-3 text-left">Payment</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php foreach ($recent as $b): ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-3">
                            <a href="bookings.php?ref=<?= urlencode($b['reference']) ?>"
                               class="font-mono text-emerald-600 hover:text-emerald-700 font-semibold text-xs">
                                <?= htmlspecialchars($b['reference']) ?>
                            </a>
                            <?php if ($b['created_by'] === 'admin'): ?>
                            <span class="ml-1 text-xs bg-purple-100 text-purple-700 px-1.5 py-0.5 rounded-full">walk-in</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-3 text-gray-700">
                            <?= htmlspecialchars($b['first_name'] . ' ' . $b['last_name']) ?>
                            <span class="text-gray-400 text-xs ml-1"><?= $b['language'] === 'Russian' ? '🇷🇺' : '🇬🇧' ?></span>
                        </td>
                        <td class="px-6 py-3 text-gray-600 max-w-[180px] truncate">
                            <?= htmlspecialchars(explode(':', $b['property_title'])[0]) ?>
                        </td>
                        <td class="px-6 py-3 text-gray-600"><?= htmlspecialchars($b['property_date']) ?></td>
                        <!-- <td class="px-6 py-3 text-gray-600"><questionmark= (int)$b['pax'] ?></td> -->
                        <td class="px-6 py-3 font-semibold text-gray-800"><?= (int)$b['price_gel'] ?>₾</td>
                        <td class="px-6 py-3">
                            <?php
                            $status_badge = match($b['status']) {
                                'confirmed' => admin_badge('Confirmed', 'green'),
                                'cancelled' => admin_badge('Cancelled', 'red'),
                                default     => admin_badge('Pending', 'amber'),
                            };
                            echo $status_badge;
                            ?>
                        </td>
                        <td class="px-6 py-3">
                            <?php
                            $pay_badge = match($b['payment_status']) {
                                'paid'    => admin_badge('Paid', 'green'),
                                'deposit' => admin_badge('Deposit', 'amber'),
                                default   => admin_badge('Unpaid', 'gray'),
                            };
                            echo $pay_badge;
                            ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($recent)): ?>
                    <tr><td colspan="8" class="px-6 py-8 text-center text-gray-400">No bookings yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<?php admin_foot(); ?>
