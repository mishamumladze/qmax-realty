<?php
declare(strict_types=1);
require_once __DIR__ . '/auth.php';
qmx_admin_require_auth();
require_once __DIR__ . '/../booking/includes/db.php';
require_once __DIR__ . '/includes/layout.php';

$db = qmx_db();

// CSV export
if (isset($_GET['export'])) {
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="newsletter-' . date('Y-m-d') . '.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Email', 'Subscribed At', 'IP']);
    foreach ($db->query("SELECT * FROM newsletter ORDER BY created_at DESC")->fetchAll() as $row) {
        fputcsv($out, [$row['email'], $row['created_at'], $row['ip'] ?? '']);
    }
    fclose($out);
    exit;
}

// Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_email'])) {
    $db->prepare("DELETE FROM newsletter WHERE email = ?")
       ->execute([trim($_POST['delete_email'])]);
    header('Location: newsletter.php');
    exit;
}

$subscribers = $db->query("SELECT * FROM newsletter ORDER BY created_at DESC")->fetchAll();
$total = count($subscribers);

admin_head('Newsletter');
admin_nav('newsletter');
admin_page_header('Newsletter Subscribers', $total . ' subscriber(s)');
?>

<div class="p-8">

    <div class="flex justify-end mb-4">
        <a href="?export=csv" class="border border-gray-300 text-gray-600 hover:bg-gray-50 text-sm font-medium px-4 py-2 rounded-lg transition-colors">↓ Export CSV</a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wide">
                <tr>
                    <th class="px-5 py-3 text-left">Email</th>
                    <th class="px-5 py-3 text-left">Subscribed</th>
                    <th class="px-5 py-3 text-left">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php foreach ($subscribers as $s): ?>
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-5 py-3 text-gray-700"><?= htmlspecialchars($s['email']) ?></td>
                    <td class="px-5 py-3 text-gray-500 text-xs"><?= htmlspecialchars($s['created_at']) ?></td>
                    <td class="px-5 py-3">
                        <form method="POST" onsubmit="return confirm('Remove this subscriber?')">
                            <input type="hidden" name="delete_email" value="<?= htmlspecialchars($s['email']) ?>">
                            <button class="text-xs border border-red-200 text-red-600 hover:bg-red-50 px-2.5 py-1 rounded-lg transition-colors">Remove</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($subscribers)): ?>
                <tr><td colspan="3" class="px-5 py-8 text-center text-gray-400">No subscribers yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>

<?php admin_foot(); ?>
