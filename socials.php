<?php
    declare(strict_types=1);
    $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
    require_once __DIR__ . '/includes/layout.php';
    
    $current_page = 'socials';
    qmx_head(
        'Connect With Us — QMAX Realty Socials',
        'Follow QMAX Real Estate on Instagram, Facebook, YouTube, TikTok, Telegram, WhatsApp & more. Stay updated with the latest property listings, market insights, and real estate opportunities in Georgia.'
    );

    $socials = [
        [
            'name'        => 'Instagram',
            'handle'      => '@qmax_realty',
            'tagline'     => 'Daily updates',
            'description' => 'Follow QMAX Realty for property photos, new listings, and standout homes across Georgia.',
            'cta_text'    => 'Follow Us',
            'cta_url'     => SOCIAL_INSTAGRAM,
            'logo'        => 'img/Logos/si-instagram-w.svg',
            'gradient'    => 'from-purple-500 to-pink-500',
        ],
        [
            'name'        => 'Facebook',
            'handle'      => 'QMAXRealty',
            'tagline'     => 'Community updates',
            'description' => 'Join our community for market insights, local tips, and exclusive property announcements.',
            'cta_text'    => 'Like Page',
            'cta_url'     => SOCIAL_FACEBOOK,
            'logo'        => 'img/Logos/si-facebook-w.svg',
            'gradient'    => 'from-blue-600 to-blue-700',
        ],
        [
            'name'        => 'TikTok',
            'handle'      => '@qmax_realty',
            'tagline'     => 'Short property clips',
            'description' => 'Quick, engaging property clips and behind-the-scenes moments from our listing team.',
            'cta_text'    => 'Follow',
            'cta_url'     => SOCIAL_TIKTOK,
            'logo'        => 'img/Logos/si-tiktok-w.svg',
            'gradient'    => 'from-black to-gray-800',
        ],
        [
            'name'        => 'Telegram',
            'handle'      => '@QMAX_Realty',
            'tagline'     => 'Instant updates',
            'description' => 'Get instant notifications about new property listings, price changes, and market news.',
            'cta_text'    => 'Join Channel',
            'cta_url'     => SOCIAL_TELEGRAM,
            'logo'        => 'img/Logos/si-telegram-w.svg',
            'gradient'    => 'from-blue-400 to-blue-500',
        ],
        [
            'name'        => 'WhatsApp',
            'handle'      => CONTACT_PHONE,
            'tagline'     => '24/7 support',
            'description' => 'Direct communication for property inquiries, viewing requests, and buying or renting support.',
            'cta_text'    => 'Message Us',
            'cta_url'     => CONTACT_WA,
            'logo'        => 'img/Logos/si-whatsapp-w.svg',
            'gradient'    => 'from-green-500 to-green-600',
        ],
    ];

?>

<?php require_once __DIR__ . '/includes/navbar.php'; ?>

<main id="swup" class="transition-fade min-h-screen container mx-auto px-4 md:py-18 py-0">

    <!-- Page Header -->
    <header class="text-center py-14 md:py-20">
        <h1 class="text-3xl md:text-4xl lg:text-5xl font-bold text-gray-800 mb-4">Connect With Us</h1>
        <p class="text-lg md:text-xl text-gray-600 max-w-3xl mx-auto">
            Follow QMAX Realty for new property listings, market insights, and investment opportunities in Georgia.
        </p>
    </header>

    <!-- Social Cards Grid -->
    <div class="max-w-6xl mx-auto">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-8">
            <?php foreach ($socials as $s): ?>
            <div class="social-card bg-gradient-to-br <?= htmlspecialchars($s['gradient'], ENT_QUOTES, 'UTF-8') ?> rounded-2xl p-6 md:p-8 text-white shadow-lg hover:shadow-2xl">

                <!-- Card Header -->
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center flex-shrink-0">
                            <img src="<?= htmlspecialchars($s['logo'], ENT_QUOTES, 'UTF-8') ?>"
                                 alt="<?= htmlspecialchars($s['name'], ENT_QUOTES, 'UTF-8') ?> logo"
                                 class="w-6 h-6" loading="lazy">
                        </div>
                        <div>
                            <h2 class="text-xl font-bold"><?= htmlspecialchars($s['name'], ENT_QUOTES, 'UTF-8') ?></h2>
                            <p class="text-white/80 text-sm"><?= htmlspecialchars($s['handle'], ENT_QUOTES, 'UTF-8') ?></p>
                        </div>
                    </div>
                    <div class="social-icon flex-shrink-0">
                        <i data-lucide="external-link" class="w-5 h-5" aria-hidden="true"></i>
                    </div>
                </div>

                <!-- Description -->
                <p class="text-white/90 mb-6 text-sm leading-relaxed">
                    <?= htmlspecialchars($s['description'], ENT_QUOTES, 'UTF-8') ?>
                </p>

                <!-- Card Footer -->
                <div class="flex items-center justify-between">
                    <span class="text-sm text-white/80"><?= htmlspecialchars($s['tagline'], ENT_QUOTES, 'UTF-8') ?></span>
                    <a href="<?= htmlspecialchars($s['cta_url'], ENT_QUOTES, 'UTF-8') ?>"
                       target="_blank" rel="noopener noreferrer"
                       class="bg-white/20 hover:bg-white/30 px-4 py-2 rounded-lg text-sm font-semibold transition-all duration-200">
                        <?= htmlspecialchars($s['cta_text'], ENT_QUOTES, 'UTF-8') ?>
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Contact Section -->
    <section class="mt-16 md:mt-20 bg-gray-50 rounded-2xl p-6 md:p-8" aria-labelledby="contact-heading">
        <div class="text-center mb-8">
            <h2 id="contact-heading" class="text-2xl md:text-3xl font-bold text-gray-800 mb-4">Get in Touch</h2>
            <p class="text-gray-600 max-w-2xl mx-auto">'Have questions about our properties or need help finding your dream home? We\'re here to help!'</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 max-w-4xl mx-auto">
            <div class="text-center">
                <div class="w-16 h-16 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i data-lucide="mail" class="w-8 h-8 text-emerald-600" aria-hidden="true"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Email</h3>
                <a href="mailto:<?= CONTACT_EMAIL ?>" class="text-emerald-600 hover:underline">
                    <?= CONTACT_EMAIL ?>
                </a>
            </div>
            <div class="text-center">
                <div class="w-16 h-16 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i data-lucide="phone" class="w-8 h-8 text-emerald-600" aria-hidden="true"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Phone</h3>
                <p class="text-gray-600">
                    <a href="tel:<?= CONTACT_PHONE ?>" class="hover:text-emerald-600"><?= CONTACT_PHONE ?></a><br>
                </p>
            </div>
            <!-- <div class="text-center">
                <div class="w-16 h-16 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i data-lucide="map-pin" class="w-8 h-8 text-emerald-600" aria-hidden="true"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Location</h3>
                <p class="text-gray-600">Tbilisi, Georgia</p>
                <a href="https://maps.app.goo.gl/BBJUDs8vDnH6VRzZ6" target="_blank" rel="noopener noreferrer"
                   class="text-emerald-600 text-sm hover:underline">View on Map</a>
            </div> -->
        </div>
    </section>

    <!-- Newsletter Section -->
    <section class="mt-12 md:mt-16 bg-emerald-50 rounded-2xl p-6 md:p-8" aria-labelledby="newsletter-heading">
        <div class="text-center mb-6">
            <h2 id="newsletter-heading" class="text-2xl md:text-3xl font-bold text-gray-800 mb-4">Stay Updated</h2>
            <p class="text-gray-600">Subscribe for new property listings, market insights, and exclusive opportunities.</p>
        </div>
        <?php $csrf_token = $_SESSION['csrf_token']; ?>
        <form action="newsletter.php" method="POST" class="max-w-md mx-auto" novalidate>
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
            <div class="flex flex-col sm:flex-row gap-3">
                <label for="newsletter-email" class="sr-only">Email address</label>
                <input type="email"
                       id="newsletter-email"
                       name="email"
                       placeholder="Enter your email"
                       required
                       autocomplete="email"
                       class="flex-1 px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                <button type="submit"
                        class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors duration-200">
                    Subscribe
                </button>
            </div>
        </form>
    </section>
</main>

<?php require_once __DIR__ . '/includes/scroll-top.php'; ?>
<?php require_once __DIR__ . '/includes/footer.php'; ?>

<?php qmx_foot(); ?>