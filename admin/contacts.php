<?php
declare(strict_types=1);
require_once __DIR__ . '/auth.php';
qmx_admin_require_auth();
require_once __DIR__ . '/../booking/includes/db.php';
require_once __DIR__ . '/includes/layout.php';

$db = qmx_db();

// ── Handle actions ──────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['id'])) {
        $stmt = $db->prepare("DELETE FROM contact_messages WHERE id = ?");
        $stmt->execute([$_POST['id']]);
        header('Location: /admin/contacts.php?deleted=1');
        exit;
    }
    
    if (isset($_POST['action']) && $_POST['action'] === 'mark_read' && isset($_POST['id'])) {
        $stmt = $db->prepare("UPDATE contact_messages SET is_read = 1 WHERE id = ?");
        $stmt->execute([$_POST['id']]);
        header('Location: /admin/contacts.php');
        exit;
    }
    
    if (isset($_POST['action']) && $_POST['action'] === 'mark_unread' && isset($_POST['id'])) {
        $stmt = $db->prepare("UPDATE contact_messages SET is_read = 0 WHERE id = ?");
        $stmt->execute([$_POST['id']]);
        header('Location: /admin/contacts.php');
        exit;
    }
    
    if (isset($_POST['action']) && $_POST['action'] === 'delete_all') {
        $db->exec("DELETE FROM contact_messages");
        header('Location: /admin/contacts.php?deleted_all=1');
        exit;
    }
}

// ── Get messages ─────────────────────────────────────────────────────────────
$status_filter = $_GET['status'] ?? 'all'; // all | read | unread
$search = trim($_GET['search'] ?? '');

$sql = "SELECT * FROM contact_messages";
$params = [];

if ($status_filter === 'read') {
    $sql .= " WHERE is_read = 1";
} elseif ($status_filter === 'unread') {
    $sql .= " WHERE is_read = 0 OR is_read IS NULL";
}

if (!empty($search)) {
    $where = (strpos($sql, 'WHERE') === false) ? ' WHERE' : ' AND';
    $sql .= " $where (first_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR subject LIKE ? OR message LIKE ?)";
    $search_param = "%$search%";
    $params = array_fill(0, 5, $search_param);
}

$sql .= " ORDER BY created_at DESC";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$messages = $stmt->fetchAll();

// ── Stats (with error handling) ─────────────────────────────────────────────
try {
    $unread_count = $db->query("SELECT COUNT(*) FROM contact_messages WHERE is_read = 0 OR is_read IS NULL")->fetchColumn();
} catch (\Throwable $e) {
    $unread_count = 0;
}

try {
    $total_count = $db->query("SELECT COUNT(*) FROM contact_messages")->fetchColumn();
} catch (\Throwable $e) {
    $total_count = 0;
}

admin_head('Contact Messages');
admin_nav('contacts');
admin_page_header('Contact Messages', 'View and manage contact form submissions');
?>

<!-- Stats -->
<div class="grid grid-cols-3 gap-6 mb-6">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="text-3xl font-bold text-gray-800"><?= $total_count ?></div>
        <div class="text-sm text-gray-500">Total Messages</div>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <div class="text-3xl font-bold text-yellow-600"><?= $unread_count ?></div>
        <div class="text-sm text-gray-500">Unread</div>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <div class="text-3xl font-bold text-emerald-600"><?= $total_count - $unread_count ?></div>
        <div class="text-sm text-gray-500">Read</div>
    </div>
</div>

<!-- Filters -->
<div class="bg-white rounded-lg shadow p-4 mb-6 flex flex-wrap items-center justify-between gap-4">
    <div class="flex gap-2">
        <a href="?status=all" class="px-4 py-2 rounded-lg text-sm font-medium <?= $status_filter === 'all' ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?>">
            All
        </a>
        <a href="?status=unread" class="px-4 py-2 rounded-lg text-sm font-medium <?= $status_filter === 'unread' ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?>">
            Unread <?php if ($unread_count > 0): ?><span class="ml-1 px-2 py-0.5 text-xs rounded-full bg-yellow-200 text-yellow-800"><?= $unread_count ?></span><?php endif; ?>
        </a>
        <a href="?status=read" class="px-4 py-2 rounded-lg text-sm font-medium <?= $status_filter === 'read' ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?>">
            Read
        </a>
    </div>
    
    <div class="flex gap-2">
        <form method="GET" class="flex gap-2">
            <input type="hidden" name="status" value="<?= htmlspecialchars($status_filter) ?>">
            <input type="text" name="search" placeholder="Search messages..." 
                   value="<?= htmlspecialchars($search) ?>"
                   class="border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
            <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                Search
            </button>
        </form>
        <?php if ($total_count > 0): ?>
        <form method="POST" onsubmit="return confirm('Delete ALL messages? This cannot be undone.')">
            <input type="hidden" name="action" value="delete_all">
            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                Delete All
            </button>
        </form>
        <?php endif; ?>
    </div>
</div>

<!-- Messages -->
<div class="bg-white rounded-lg shadow overflow-hidden">
    <?php if (empty($messages)): ?>
        <div class="p-12 text-center text-gray-500">
            <i data-lucide="inbox" class="w-12 h-12 mx-auto mb-4 text-gray-300"></i>
            <p class="text-lg font-medium">No messages found</p>
            <p class="text-sm">Contact form submissions will appear here</p>
        </div>
    <?php else: ?>
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subject</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            <?php foreach ($messages as $msg): ?>
            <?php $is_unread = empty($msg['is_read']) || $msg['is_read'] == 0; ?>
            <tr class="<?= $is_unread ? 'bg-yellow-50 hover:bg-yellow-100' : 'hover:bg-gray-50' ?>">
                <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                    <?= date('M j, Y g:i A', strtotime($msg['created_at'])) ?>
                </td>
                <td class="px-6 py-4 text-sm font-medium text-gray-900">
                    <?= htmlspecialchars($msg['first_name'] . ' ' . $msg['last_name']) ?>
                </td>
                <td class="px-6 py-4 text-sm">
                    <a href="mailto:<?= htmlspecialchars($msg['email']) ?>" class="text-emerald-600 hover:underline">
                        <?= htmlspecialchars($msg['email']) ?>
                    </a>
                </td>
                <td class="px-6 py-4 text-sm text-gray-500">
                    <?= htmlspecialchars($msg['subject']) ?>
                </td>
                <td class="px-6 py-4 text-sm">
                    <?php if ($is_unread): ?>
                        <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800">New</span>
                    <?php else: ?>
                        <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-600">Read</span>
                    <?php endif; ?>
                </td>
                <td class="px-6 py-4 text-sm space-x-2">
                    <button onclick="toggleMessage(<?= $msg['id'] ?>)" class="text-blue-600 hover:text-blue-800">
                        View
                    </button>
                    
                    <?php if ($is_unread): ?>
                    <form method="POST" class="inline">
                        <input type="hidden" name="action" value="mark_read">
                        <input type="hidden" name="id" value="<?= $msg['id'] ?>">
                        <button type="submit" class="text-emerald-600 hover:text-emerald-800">Mark Read</button>
                    </form>
                    <?php else: ?>
                    <form method="POST" class="inline">
                        <input type="hidden" name="action" value="mark_unread">
                        <input type="hidden" name="id" value="<?= $msg['id'] ?>">
                        <button type="submit" class="text-yellow-600 hover:text-yellow-800">Mark Unread</button>
                    </form>
                    <?php endif; ?>
                    
                    <form method="POST" class="inline" onsubmit="return confirm('Delete this message?')">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= $msg['id'] ?>">
                        <button type="submit" class="text-red-600 hover:text-red-800">Delete</button>
                    </form>
                </td>
            </tr>
            <tr id="message-<?= $msg['id'] ?>" style="display:none;">
                <td colspan="6" class="px-6 py-4 bg-gray-50">
                    <div class="bg-white rounded-lg p-4 border border-gray-200">
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <p class="text-sm font-medium text-gray-900">
                                    <?= htmlspecialchars($msg['first_name'] . ' ' . $msg['last_name']) ?>
                                </p>
                                <p class="text-sm text-gray-500">
                                    <a href="mailto:<?= htmlspecialchars($msg['email']) ?>" class="text-emerald-600 hover:underline">
                                        <?= htmlspecialchars($msg['email']) ?>
                                    </a>
                                    <?php if ($msg['phone']): ?>
                                        · <a href="tel:<?= htmlspecialchars($msg['phone']) ?>" class="text-emerald-600 hover:underline">
                                            <?= htmlspecialchars($msg['phone']) ?>
                                        </a>
                                    <?php endif; ?>
                                </p>
                            </div>
                            <span class="text-xs text-gray-400">IP: <?= htmlspecialchars($msg['ip'] ?? 'N/A') ?></span>
                        </div>
                        <div class="bg-gray-50 rounded p-3 border border-gray-100">
                            <p class="text-sm text-gray-700 whitespace-pre-wrap"><?= nl2br(htmlspecialchars($msg['message'])) ?></p>
                        </div>
                        <div class="mt-2 flex gap-2">
                            <a href="mailto:<?= htmlspecialchars($msg['email']) ?>?subject=Re: <?= urlencode($msg['subject']) ?>" 
                               class="text-sm text-emerald-600 hover:text-emerald-800">
                                Reply via Email
                            </a>
                        </div>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<script>
function toggleMessage(id) {
    var row = document.getElementById('message-' + id);
    if (row.style.display === 'none' || row.style.display === '') {
        row.style.display = 'table-row';
    } else {
        row.style.display = 'none';
    }
}

// Auto-expand if URL has #message-123
document.addEventListener('DOMContentLoaded', function() {
    if (window.location.hash && window.location.hash.startsWith('#message-')) {
        var id = window.location.hash.replace('#message-', '');
        toggleMessage(id);
    }
});
</script>

<?php admin_foot(); ?>