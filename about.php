<?php

declare(strict_types=1);

http_response_code(404);
require_once __DIR__ . '/includes/layout.php';

$current_page = '';

qmx_head(
    'Page Not Found — QMAX Realty',
    'The page you are looking for could not be found. Browse our premium real estate listings instead.'
);

?>

<?php require_once __DIR__ . '/includes/navbar.php'; ?>

<main class="min-h-screen flex items-center justify-center px-4">
    <div class="text-center max-w-lg mx-auto py-20">
        <div class="text-emerald-600 text-9xl font-black mb-4" aria-hidden="true">404</div>
        <h1 class="text-3xl font-bold text-gray-800 mb-4">Page Not Found</h1>
        <p class="text-gray-600 mb-8">
            The page you're looking for doesn't exist or may have moved.
            Let's get you back on the right path.
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="<?= BASE_URL ?>" class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors duration-200">
                Go Home
            </a>
            <a href="<?= BASE_URL ?>listings" class="border-2 border-emerald-600 text-emerald-600 hover:bg-emerald-50 px-6 py-3 rounded-lg font-semibold transition-colors duration-200">
                Browse Properties
            </a>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
<?php qmx_foot(); ?>