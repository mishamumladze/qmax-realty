<?php
declare(strict_types=1);

/**
 * QMAX Realty — Navbar partial
 * Uses BASE_URL constant defined in config/app.php.
 */

$base = BASE_URL;
?>
<nav id="navbar" class="fixed bottom-0 md:top-0 md:bottom-auto z-50 w-full bg-white/95 backdrop-blur-lg border-t md:border-t-0 md:border-b border-gray-200 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">

            <!-- Logo -->
            <div class="flex-1 flex items-center">
                <a href="<?= $base ?>index" class="flex-shrink-0 flex items-center space-x-2">
                    <img src="<?= $base ?>img/Logo200.webp" alt="QMAX Realty Logo" class="h-8 w-8 md:h-10 md:w-10" width="40" height="40">
                    <span class="font-bold text-lg md:text-xl text-gray-800">QMAX Realty</span>
                </a>
            </div>

            <!-- Desktop nav links -->
            <div class="hidden md:block">
                <div class="flex items-baseline space-x-6">
                    <a href="<?= $base ?>index" data-nav-page="home"
                       class="text-gray-600 hover:text-emerald-600 px-3 py-2 rounded-md text-sm font-medium transition-colors duration-200">
                        Home
                    </a>
                    <a href="<?= $base ?>socials" data-nav-page="socials"
                       class="text-gray-600 hover:text-emerald-600 px-3 py-2 rounded-md text-sm font-medium transition-colors duration-200">
                        Socials
                    </a>
                    <a href="<?= $base ?>listings" data-nav-page="listings"
                       class="text-gray-600 hover:text-emerald-600 px-3 py-2 rounded-md text-sm font-medium transition-colors duration-200">
                        Listings
                    </a>
                    <a href="<?= $base ?>contact" data-nav-page="contact"
                       class="text-gray-600 hover:text-emerald-600 px-3 py-2 rounded-md text-sm font-medium transition-colors duration-200">
                        Contact
                    </a>
                    <a href="<?= $base ?>about" data-nav-page="about"
                       class="text-gray-600 hover:text-emerald-600 px-3 py-2 rounded-md text-sm font-medium transition-colors duration-200">
                        About
                    </a>
                </div>
            </div>

            <!-- Right: language switcher + hamburger -->
            <div class="flex-1 flex items-center justify-end gap-3">
                <div class="gtranslate_wrapper"></div>
                <div class="flex md:hidden">
                    <button id="mobile-menu-toggle" type="button"
                            class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-gray-600 hover:text-emerald-600 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 transition-all duration-200"
                            aria-label="Open main menu" aria-expanded="false" aria-controls="slide-menu">
                        <i data-lucide="menu" id="hamburger-icon" class="h-6 w-6 transition-all duration-300 ease-in-out"></i>
                        <i data-lucide="x"    id="close-icon"     class="hidden h-6 w-6 transition-all duration-300 ease-in-out"></i>
                    </button>
                </div>
            </div>

        </div>
    </div>
</nav>

<!-- Slide-up mobile menu -->
<div id="slide-menu" class="md:hidden fixed inset-x-0 bottom-0 z-40 transform transition-transform duration-300 ease-in-out translate-y-full">
    <div class="bg-white rounded-t-2xl shadow-2xl border-t-4 border-emerald-500 max-h-[80vh] overflow-y-auto safe-area-bottom">
        <div class="flex justify-center pt-3 pb-1 cursor-pointer hover:bg-emerald-500 transition-colors duration-200" id="slide-menu-bar">
            <div class="w-12 h-1.5 bg-gray-300 rounded-full"></div>
        </div>
        <div class="px-4 pt-2 pb-12 space-y-1">
            <?php
            $mobileLinks = [
                ['href' => 'index',    'page' => 'home',     'label' => 'Home',     'icon' => 'home'],
                ['href' => 'socials',  'page' => 'socials',  'label' => 'Socials',  'icon' => 'share-2'],
                ['href' => 'listings', 'page' => 'listings', 'label' => 'Listings', 'icon' => 'users'],
                ['href' => 'contact',  'page' => 'contact',  'label' => 'Contact',  'icon' => 'building-2'],
                ['href' => 'about',    'page' => 'about',    'label' => 'About',    'icon' => 'info'],
            ];
            ?>
            <?php foreach ($mobileLinks as $ml): ?>
                <a href="<?= $base . $ml['href'] ?>" data-nav-page="<?= $ml['page'] ?>"
                   class="flex items-center justify-between px-4 py-3 rounded-xl text-base font-medium transition-all duration-200 text-gray-600 hover:text-emerald-600 hover:bg-emerald-50">
                    <span><?= $ml['label'] ?></span>
                    <i data-lucide="<?= $ml['icon'] ?>" class="w-5 h-5 text-emerald-600"></i>
                </a>
            <?php endforeach; ?>

            <div class="border-t border-gray-200 my-3"></div>
        </div>
    </div>
</div>

<!-- Overlay -->
<div id="slide-overlay" class="fixed inset-0 bg-black/40 backdrop-blur-sm z-30 hidden transition-opacity duration-300 opacity-0"></div>