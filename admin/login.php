<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config/app.php';

if (qmx_admin_logged_in()) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bf = qmx_admin_brute_state();

    if ($bf['locked']) {
        $mins  = ceil($bf['remaining'] / 60);
        $error = "Too many failed attempts. Try again in {$mins} minute(s).";
    } else {
        $submitted = $_POST['password'] ?? '';

        if (ADMIN_PASSWORD === '') {
            $error = 'Admin password not configured in secrets.php.';
        } elseif (hash_equals(ADMIN_PASSWORD, $submitted)) {
            qmx_admin_clear_failures();
            $_SESSION['admin_logged_in']   = true;
            $_SESSION['admin_last_active'] = time();
            session_regenerate_id(true);
            header('Location: index.php');
            exit;
        } else {
            qmx_admin_record_failure();
            $bf   = qmx_admin_brute_state();
            $left = ADMIN_MAX_ATTEMPTS - $bf['attempts'];
            $error = $left > 0
                ? "Incorrect password. {$left} attempt(s) remaining."
                : 'Too many failed attempts. Account locked for 15 minutes.';
        }
    }
}

$base = BASE_URL;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login — QMAX Realty</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="stylesheet" href="<?= $base ?>scss/main.css">
    <link rel="icon" type="image/webp" href="<?= $base ?>img/Logo200.webp">
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="bg-white rounded-2xl shadow-lg p-8 w-full max-w-sm">
        <div class="text-center mb-8">
            <img src="<?= $base ?>img/Logo200.webp" alt="QMAX Realty" class="w-16 h-16 mx-auto mb-4 rounded-full">
            <h1 class="text-2xl font-bold text-gray-800">Admin Panel</h1>
            <p class="text-gray-500 text-sm mt-1">QMAX Realty</p>
        </div>

        <?php if ($error): ?>
        <div class="bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg px-4 py-3 mb-4">
            <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
        </div>
        <?php endif; ?>

        <form method="POST" novalidate>
            <div class="mb-4">
                <label for="password" class="block text-sm font-semibold text-gray-700 mb-1">Password</label>
                <input type="password" id="password" name="password" required autofocus
                       class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </div>
            <button type="submit"
                    class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 px-6 rounded-xl transition-colors duration-200">
                Sign In
            </button>
        </form>
    </div>
</body>
</html>