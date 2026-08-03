<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/layout.php';

qmx_head(
    'About QMAX Realty — Trusted Real Estate Agency in Georgia',
    'Learn about QMAX Realty — an international real estate agency serving clients in Georgia and beyond. Expert guidance for buying, selling, and renting premium property.'
);

?>

<?php require_once __DIR__ . '/includes/navbar.php'; ?>

<main id="swup" class="transition-fade min-h-screen container mx-auto px-4 md:py-18 py-0">

    <!-- Hero -->
    <section class="relative bg-gradient-to-r from-emerald-600 to-emerald-700 text-white py-16 md:py-24 rounded-2xl mb-12 overflow-hidden">
        <div class="relative container mx-auto px-4 text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-4">About QMAX Realty</h1>
            <p class="text-lg md:text-xl max-w-2xl mx-auto opacity-90">
                Your trusted partner for buying, selling, and renting premium property in Georgia.
            </p>
        </div>
    </section>

    <!-- Our Story -->
    <section class="grid grid-cols-1 lg:grid-cols-2 gap-10 items-center mb-16" id="our-story">
        <div class="rounded-2xl overflow-hidden shadow-lg">
            <img src="<?= BASE_URL ?>img/hero.webp" alt="Premium properties with QMAX Realty" class="w-full h-72 md:h-[420px] object-cover">
        </div>
        <div>
            <h2 class="text-3xl font-bold text-gray-800 mb-4">Our Story</h2>
            <p class="text-gray-600 leading-relaxed mb-4">
                QMAX Realty is an international real estate agency built on a simple promise:
                honest guidance, transparent deals, and a genuine understanding of the local market.
                From historic Old Town apartments to modern luxury penthouses, we help buyers,
                sellers, and investors navigate Georgia's property market with confidence.
            </p>
            <p class="text-gray-600 leading-relaxed mb-6">
                Whether you're searching for your first home in the city or expanding an investment
                portfolio, our team combines local knowledge with international standards of service —
                and we speak your language.
            </p>
            <div class="flex flex-col sm:flex-row gap-4">
                <a href="<?= BASE_URL ?>listings" class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors duration-200 text-center">
                    Browse Properties
                </a>
                <a href="<?= BASE_URL ?>contact" class="border-2 border-emerald-600 text-emerald-600 hover:bg-emerald-50 px-6 py-3 rounded-lg font-semibold transition-colors duration-200 text-center">
                    Get in Touch
                </a>
            </div>
        </div>
    </section>

    <!-- Stats -->
    <section class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-16" id="stats">
        <?php
        // NOTE: draft figures — update with real agency numbers before launch.
        $stats = [
            ['value' => '500+',   'label' => 'Properties Listed',        'icon' => 'building-2'],
            ['value' => '300+',   'label' => 'Happy Clients',            'icon' => 'badge-check'],
            ['value' => '10+',    'label' => 'Years of Experience',      'icon' => 'award'],
            ['value' => '< 24h',  'label' => 'Avg. Response Time',       'icon' => 'clock'],
        ];
        foreach ($stats as $stat): ?>
            <div class="bg-white rounded-2xl shadow-sm hover:shadow-md transition-shadow p-6 border border-gray-100 text-center">
                <div class="w-12 h-12 bg-emerald-50 rounded-xl flex items-center justify-center mx-auto mb-3">
                    <i data-lucide="<?= $stat['icon'] ?>" class="w-5 h-5 text-emerald-600"></i>
                </div>
                <p class="text-3xl font-bold text-gray-800"><?= $stat['value'] ?></p>
                <p class="text-sm text-gray-500 mt-1"><?= $stat['label'] ?></p>
            </div>
        <?php endforeach; ?>
    </section>

    <!-- What We Do -->
    <section class="mb-16" id="services">
        <h2 class="text-3xl font-bold text-gray-800 mb-2">What We Do</h2>
        <p class="text-gray-600 mb-8">Full-cycle support across every stage of your property journey.</p>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <?php
            $services = [
                ['icon' => 'home',       'title' => 'Buying',    'text' => 'Carefully selected listings matched to your budget, lifestyle, and investment goals.'],
                ['icon' => 'key-round',  'title' => 'Selling',   'text' => 'Accurate valuations, professional photography, and targeted marketing to sell faster.'],
                ['icon' => 'building-2', 'title' => 'Renting',   'text' => 'Verified rental properties with clear contracts and reliable long-term tenancy support.'],
                ['icon' => 'trending-up','title' => 'Investing', 'text' => 'Market research and yield analysis to help you make confident investment decisions.'],
            ];
            foreach ($services as $service): ?>
                <div class="bg-white rounded-2xl shadow-sm hover:shadow-md transition-shadow p-6 border border-gray-100">
                    <div class="w-12 h-12 bg-emerald-50 rounded-xl flex items-center justify-center mb-4">
                        <i data-lucide="<?= $service['icon'] ?>" class="w-5 h-5 text-emerald-600"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-2"><?= $service['title'] ?></h3>
                    <p class="text-sm text-gray-600 leading-relaxed"><?= $service['text'] ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Why Choose Us -->
    <section class="bg-gray-50 rounded-2xl p-8 md:p-12 mb-16" id="why-us">
        <h2 class="text-3xl font-bold text-gray-800 mb-2 text-center">Why Choose QMAX Realty</h2>
        <p class="text-gray-600 mb-10 text-center">What sets us apart in the Georgian market.</p>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php
            $reasons = [
                ['icon' => 'map-pin',     'title' => 'Local Expertise', 'text' => 'Deep knowledge of Tbilisi neighborhoods — from Sololaki to Vake and beyond.'],
                ['icon' => 'globe',       'title' => 'Multilingual Team', 'text' => 'We work with international clients in English, Russian, and Georgian.'],
                ['icon' => 'shield-check','title' => 'Transparent Deals', 'text' => 'Clear contracts, verified documentation, and no hidden fees.'],
                ['icon' => 'handshake',   'title' => 'Personal Service', 'text' => 'A dedicated agent who knows your needs and stays with you start to finish.'],
                ['icon' => 'clock',       'title' => 'Fast Response',   'text' => 'Questions answered within hours, viewings arranged at your convenience.'],
                ['icon' => 'badge-check', 'title' => 'Verified Listings','text' => 'Every property is checked and represented honestly — what you see is what you get.'],
            ];
            foreach ($reasons as $reason): ?>
                <div class="bg-white rounded-2xl shadow-sm hover:shadow-md transition-shadow p-6 border border-gray-100">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-10 h-10 bg-emerald-50 rounded-lg flex items-center justify-center flex-shrink-0">
                            <i data-lucide="<?= $reason['icon'] ?>" class="w-5 h-5 text-emerald-600"></i>
                        </div>
                        <h3 class="font-semibold text-gray-800"><?= $reason['title'] ?></h3>
                    </div>
                    <p class="text-sm text-gray-600 leading-relaxed"><?= $reason['text'] ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- CTA -->
    <section class="bg-gradient-to-r from-emerald-600 to-emerald-700 text-white rounded-2xl p-10 md:p-14 text-center mb-16">
        <h2 class="text-3xl font-bold mb-3">Ready to Find Your Property?</h2>
        <p class="text-lg opacity-90 max-w-2xl mx-auto mb-8">
            Talk to our team today and let us help you make the right move in the Georgian market.
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="<?= CONTACT_WA ?>" target="_blank" rel="noopener noreferrer"
               class="bg-white text-emerald-700 hover:bg-emerald-50 px-6 py-3 rounded-lg font-semibold transition-colors duration-200">
                Chat on WhatsApp
            </a>
            <a href="<?= BASE_URL ?>contact"
               class="border-2 border-white text-white hover:bg-white/10 px-6 py-3 rounded-lg font-semibold transition-colors duration-200">
                Contact Us
            </a>
        </div>
    </section>

</main>

<?php require_once __DIR__ . '/includes/scroll-top.php'; ?>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
<?php qmx_foot(); ?>
