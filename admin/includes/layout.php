<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/app.php';

function admin_head(string $title): void {
    $base = BASE_URL;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?> — QMX Admin</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="stylesheet" href="<?= $base ?>scss/main.css">
    <link rel="icon" type="image/webp" href="<?= $base ?>img/Logo200.webp">
</head>
<body class="bg-gray-100 min-h-screen">
<?php }

function admin_nav(string $current): void {
    $base = BASE_URL;
    $nav  = [
        'index'       => ['label' => 'Dashboard',     'icon' => 'home'],
        'bookings'    => ['label' => 'Bookings',      'icon' => 'clipboard-list'],
        'new-booking' => ['label' => 'Walk-in',       'icon' => 'plus-circle'],
        'promoters'   => ['label' => 'Promoters',     'icon' => 'users'],
        'prices'      => ['label' => 'Prices',        'icon' => 'tag'],
        'newsletter'  => ['label' => 'Newsletter',    'icon' => 'mail'],
        'exchange'    => ['label' => 'Exchange Rate', 'icon' => 'arrow-left-right'],
        'contacts' => ['label' => 'Contact Messages', 'icon' => 'mail'],  // changed from 'users'
    ];
?>
    <aside class="fixed top-0 left-0 h-full w-56 bg-gray-900 flex flex-col z-30">
        <div class="px-4 py-5 border-b border-white/10">
            <p class="text-white font-bold text-base leading-tight">QMAX Realty</p>
            <p class="text-gray-400 text-xs mt-0.5">Admin Panel</p>
        </div>
        <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
            <?php foreach ($nav as $page => $item):
                $is_active = $current === $page;
                $cls = $is_active
                    ? 'flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium bg-emerald-600 text-white'
                    : 'flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium text-gray-300 hover:bg-white/10 hover:text-white transition-colors duration-200';
            ?>
            <a href="<?= $page ?>" class="<?= $cls ?>">
                <i data-lucide="<?= $item['icon'] ?>" class="w-4 h-4 flex-shrink-0"></i>
                <?= $item['label'] ?>
            </a>
            <?php endforeach; ?>
        </nav>
        <div class="px-3 py-4 border-t border-white/10">
            <a href="logout.php"
               class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium text-red-400 hover:text-red-300 hover:bg-red-900/20 transition-colors duration-200">
                <i data-lucide="log-out" class="w-4 h-4 flex-shrink-0"></i>
                Logout
            </a>
        </div>
    </aside>
    <main class="ml-56 min-h-screen">
<?php }

function admin_foot(): void {
    $base = BASE_URL;
?>
    </main>
    <script src="<?= $base ?>js/lucide.min.js"></script>
    <script src="<?= $base ?>js/swup.min.js"></script>
    <script>
        if (typeof lucide !== 'undefined') lucide.createIcons();
    </script>
</body>
</html>
<?php }

function admin_page_header(string $title, string $subtitle = ''): void { ?>
    <div class="bg-white border-b border-gray-200 px-8 py-5">
        <h1 class="text-xl font-bold text-gray-800"><?= htmlspecialchars($title) ?></h1>
        <?php if ($subtitle): ?>
        <p class="text-sm text-gray-500 mt-0.5"><?= htmlspecialchars($subtitle) ?></p>
        <?php endif; ?>
    </div>
<?php }

function admin_badge(string $text, string $color): string {
    $colors = [
        'green'  => 'bg-green-100 text-green-800',
        'emerald'  => 'bg-emerald-100 text-emerald-800',
        'red'    => 'bg-red-100 text-red-800',
        'gray'   => 'bg-gray-100 text-gray-700',
        'blue'   => 'bg-blue-100 text-blue-800',
        'purple' => 'bg-purple-100 text-purple-800',
    ];
    $cls = $colors[$color] ?? $colors['gray'];
    return '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold ' . $cls . '">'
        . htmlspecialchars($text) . '</span>';
}