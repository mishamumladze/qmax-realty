<?php
    declare(strict_types=1);
    session_start(); // Add this!
    $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
    require_once __DIR__ . '/includes/layout.php';

    $current_page = 'home';
    qmx_head(
        'QMAX Real Estate — Find Your Dream Property!',
        'Discover premium real estate in Georgia. Buy, sell, or rent properties in Tbilisi, Batumi, and beyond. Expert guidance, transparent transactions, and prime locations.',
    );

    $all_properties = require __DIR__ . '/config/properties.php';
    // Featured Properties
    $featured_slugs = ['sololaki-luxury-penthouse', 'vera-garden-apartment', 'saburtalo-family-house', 'vake-city-view-apartment', 'old-town-charming-studio', 'tbilisi-sea-luxury-villa'];
?>
<main class="min-h-screen container mx-auto px-4 md:py-18 py-0">

<!-- Hero -->
<section class="relative w-full h-[68vh]" aria-label="Featured properties slideshow">
    <div class="min-w-full h-full">
        <img src="img/hero.webp" alt="Premium real estate properties with stunning views"
             class="w-full h-full object-cover object-[50%_35%] rounded-lg" loading="eager">
        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent flex flex-col md:py-24 py-16 text-center rounded-lg">
            <div class="w-4/5 mx-auto text-white mb-6 h-full justify-end h-full">
                <h1 class="text-3xl md:text-5xl font-bold mb-2">Find Your Dream Property!</h1>
                <p class="text-base md:text-lg max-w-2xl mx-auto mb-6">Discover premium real estate with expert guidance and unmatched service</p>
            </div>
            <div class="">
                <!-- Each button links to properties.php with the right ?filter= so the tab is pre-selected -->
                <a href="<?= BASE_URL ?>listings?offer=sale"
                   class="inline-flex items-center gap-2 hover:bg-emerald-500 active:bg-emerald-700 text-white font-bold text-base md:text-lg px-4 py-3 rounded-full shadow-lg transition-all duration-200 hover:shadow-emerald-500/40 hover:shadow-xl hover:-translate-y-0.5 m-2 bg-emerald-600/80">
                    <i data-lucide="home" class="w-5 h-5 opacity-100" aria-hidden="true"></i>
                    Buy Properties
                </a>
                <a href="<?= BASE_URL ?>listings?offer=rent"
                   class="inline-flex items-center gap-2 hover:bg-emerald-500 active:bg-emerald-700 text-white font-bold text-base md:text-lg px-4 py-3 rounded-full shadow-lg transition-all duration-200 hover:shadow-emerald-500/40 hover:shadow-xl hover:-translate-y-0.5 m-2 bg-emerald-600/80">
                    <i data-lucide="key" class="w-5 h-5 opacity-100" aria-hidden="true"></i>
                    Rent Properties
                </a>
                <a href="<?= BASE_URL ?>contact?subject=selling"
                   class="inline-flex items-center gap-2 hover:bg-emerald-500 active:bg-emerald-700 text-white font-bold text-base md:text-lg px-4 py-3 rounded-full shadow-lg transition-all duration-200 hover:shadow-emerald-500/40 hover:shadow-xl hover:-translate-y-0.5 m-2 bg-emerald-600/80">
                    <i data-lucide="badge-dollar-sign" class="w-5 h-5 opacity-100" aria-hidden="true"></i>
                    Sell Your Home
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Most Viewed Properties This Week — Carousel -->
<section class="container mx-auto px-4 py-8 md:py-12" aria-labelledby="most-viewed-heading">
    <div class="text-center mb-8 md:mb-12">
        <h2 id="most-viewed-heading" class="text-2xl md:text-3xl lg:text-4xl font-bold text-emerald-600 mb-4">
            Most Viewed Properties This Week
        </h2>
        <p class="text-base md:text-lg text-gray-600 max-w-2xl mx-auto">
            These properties are trending with buyers and investors right now
        </p>
    </div>

    <!-- Carousel wrapper -->
    <div class="relative max-w-5xl mx-auto" id="properties-carousel-wrapper">

        <!-- Left arrow -->
        <button id="properties-carousel-prev"
                aria-label="Previous properties"
                class="absolute left-0 top-1/2 -translate-y-1/2 -translate-x-4 md:-translate-x-6 z-10
                       w-10 h-10 flex items-center justify-center
                       bg-white border border-gray-200 rounded-full shadow-md
                       text-gray-600 hover:text-emerald-600 hover:border-emerald-400
                       transition-all duration-200 disabled:opacity-30 disabled:cursor-not-allowed">
            <i data-lucide="chevron-left" class="w-5 h-5" aria-hidden="true"></i>
        </button>

        <!-- Clipping window -->
        <div class="overflow-hidden" id="properties-carousel-clip">
            <!-- Scrolling track -->
            <div class="flex gap-4 md:gap-6 transition-transform duration-400 ease-in-out will-change-transform"
                 id="properties-carousel-track">
                <?php foreach ($featured_slugs as $slug):
                    $p = $all_properties[$slug] ?? null;
                    if (!$p) continue;
                ?>
                <article class="properties-carousel-card flex-none w-[80vw] sm:w-[45%] lg:w-[31%]
                                flex flex-col bg-white rounded-xl shadow-lg overflow-hidden
                                hover:shadow-xl transition-shadow duration-300">
                    <img src="<?= htmlspecialchars((string)$p['card_image'], ENT_QUOTES, 'UTF-8') ?>"
                         alt="<?= htmlspecialchars((string)$p['title'], ENT_QUOTES, 'UTF-8') ?>"
                         class="w-full h-40 md:h-48 object-cover"
                         loading="lazy">
                    <div class="p-4 md:p-6 flex-1 flex flex-col">
                        <div class="flex justify-between items-start mb-2">
                            <h3 class="text-lg md:text-xl font-bold text-gray-800">
                                <?= htmlspecialchars((string)$p['title'], ENT_QUOTES, 'UTF-8') ?>
                            </h3>
                            <span class="text-emerald-600 font-bold text-sm md:text-base">
                                $<?= htmlspecialchars((string)$p['price'], ENT_QUOTES, 'UTF-8') ?>
                            </span>
                        </div>
                        <p class="text-sm md:text-base text-gray-600 mb-3 md:mb-4">
                            <?= htmlspecialchars((string)$p['location'], ENT_QUOTES, 'UTF-8') ?>
                        </p>
                        <div class="flex gap-4 text-xs md:text-sm text-gray-500 mb-3">
                            <span><i data-lucide="bed" class="w-4 h-4 inline mr-1"></i> <?= htmlspecialchars((string)$p['bedrooms'], ENT_QUOTES, 'UTF-8') ?></span>
                            <span><i data-lucide="bath" class="w-4 h-4 inline mr-1"></i> <?= htmlspecialchars((string)$p['bathrooms'], ENT_QUOTES, 'UTF-8') ?></span>
                            <span><i data-lucide="square" class="w-4 h-4 inline mr-1"></i> <?= htmlspecialchars((string)$p['sqft'], ENT_QUOTES, 'UTF-8') ?> ft²</span>
                        </div>
                        <a href="<?= BASE_URL ?>properties/details/<?= htmlspecialchars((string)$p['slug'], ENT_QUOTES, 'UTF-8') ?>"
                           class="learn-more-btn mt-auto inline-flex items-center text-emerald-600 hover:text-emerald-700 font-semibold transition-all duration-300">
                            <span class="learn-more-text">View Details</span>
                            <i data-lucide="arrow-right" class="learn-more-arrow w-4 h-4 ml-1 transition-all duration-300" aria-hidden="true"></i>
                        </a>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Right arrow -->
        <button id="properties-carousel-next"
                aria-label="Next properties"
                class="absolute right-0 top-1/2 -translate-y-1/2 translate-x-4 md:translate-x-6 z-10
                       w-10 h-10 flex items-center justify-center
                       bg-white border border-gray-200 rounded-full shadow-md
                       text-gray-600 hover:text-emerald-600 hover:border-emerald-400
                       transition-all duration-200 disabled:opacity-30 disabled:cursor-not-allowed">
            <i data-lucide="chevron-right" class="w-5 h-5" aria-hidden="true"></i>
        </button>

        <!-- Dot indicators -->
        <div class="flex justify-center gap-2 mt-6" id="properties-carousel-dots" aria-hidden="true"></div>
    </div>

    <script>
    (function () {
        'use strict';

        const track   = document.getElementById('properties-carousel-track');
        const clip    = document.getElementById('properties-carousel-clip');
        const btnPrev = document.getElementById('properties-carousel-prev');
        const btnNext = document.getElementById('properties-carousel-next');
        const dotsEl  = document.getElementById('properties-carousel-dots');

        if (!track || !clip || !btnPrev || !btnNext) return;

        // How many cards are visible at once (matches CSS breakpoints)
        function visibleCount() {
            const w = window.innerWidth;
            if (w >= 1024) return 3;
            if (w >= 640)  return 2;
            return 1;
        }

        const cards    = Array.from(track.querySelectorAll('.properties-carousel-card'));
        const total    = cards.length;
        let   position = 0; // index of the first visible card

        // Build dot indicators
        function buildDots() {
            dotsEl.innerHTML = '';
            const steps = total - visibleCount() + 1;
            for (let i = 0; i < steps; i++) {
                const d = document.createElement('button');
                d.className = 'w-2 h-2 rounded-full transition-colors duration-200 ' +
                              (i === 0 ? 'bg-emerald-500' : 'bg-gray-300');
                d.setAttribute('aria-label', 'Go to slide ' + (i + 1));
                d.addEventListener('click', () => { position = i; render(); });
                dotsEl.appendChild(d);
            }
        }

        function cardWidth() {
            // card outer width including gap
            if (cards.length === 0) return 0;
            const style = getComputedStyle(track);
            const gap   = parseFloat(style.gap) || 16;
            return cards[0].offsetWidth + gap;
        }

        function render() {
            const vc       = visibleCount();
            const maxPos   = Math.max(0, total - vc);
            position       = Math.min(Math.max(position, 0), maxPos);

            track.style.transform = 'translateX(-' + (position * cardWidth()) + 'px)';

            btnPrev.disabled = (position === 0);
            btnNext.disabled = (position >= maxPos);

            // Update dots
            const dots = dotsEl.querySelectorAll('button');
            dots.forEach((d, i) => {
                d.className = 'w-2 h-2 rounded-full transition-colors duration-200 ' +
                              (i === position ? 'bg-emerald-500' : 'bg-gray-300');
            });
        }

        btnPrev.addEventListener('click', () => { position--; render(); });
        btnNext.addEventListener('click', () => { position++; render(); });

        // Touch / swipe support
        let touchStartX = 0;
        clip.addEventListener('touchstart', e => { touchStartX = e.touches[0].clientX; }, { passive: true });
        clip.addEventListener('touchend',   e => {
            const dx = touchStartX - e.changedTouches[0].clientX;
            if (Math.abs(dx) > 40) { dx > 0 ? position++ : position--; render(); }
        }, { passive: true });

        // Re-render on resize (responsive column count may change)
        let resizeTimer;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(() => { buildDots(); render(); }, 150);
        });

        buildDots();
        render();
    })();
    </script>
</section>

<!-- Why Choose Us -->
<section class="container mx-auto px-4 py-12 md:py-16 bg-gray-50 rounded-2xl my-8" aria-labelledby="why-us-heading">
    <div class="text-center mb-12">
        <h2 id="why-us-heading" class="text-3xl md:text-4xl font-bold text-gray-800 mb-12">Why Choose QMAX Realty?</h2>
        <!-- Stats row -->
        <div class="grid grid-cols-3 gap-6 max-w-3xl mx-auto">
            <div class="text-center">
                <p class="text-3xl md:text-4xl font-black text-emerald-600">15+</p>
                <p class="text-sm md:text-base font-medium text-gray-600 mt-1">Years of Excellence</p>
            </div>
            <div class="text-center">
                <p class="text-3xl md:text-4xl font-black text-emerald-600">1,200+</p>
                <p class="text-sm md:text-base font-medium text-gray-600 mt-1">Properties Sold</p>
            </div>
            <div class="text-center">
                <p class="text-3xl md:text-4xl font-black text-emerald-600">99%</p>
                <p class="text-sm md:text-base font-medium text-gray-600 mt-1">Client Satisfaction</p>
            </div>
        </div>
    </div>
    <div class="grid grid-cols-4 gap-8 max-w-6xl mx-auto">
        <?php
        $why_us = [
            ['icon' => 'building-2',    'title' => 'Market Experts',   'desc' => 'Deep knowledge of local real estate markets, trends, and investment opportunities.'],
            ['icon' => 'star',          'title' => '5★ Service',       'desc' => 'Hundreds of 5-star reviews from satisfied buyers, sellers, and investors.'],
            ['icon' => 'handshake',     'title' => 'Personalized Approach', 'desc' => 'Tailored solutions and one-on-one guidance for your unique real estate needs.'],
            ['icon' => 'shield-check',  'title' => 'Trusted & Transparent', 'desc' => 'Clear communication, ethical practices, and full support throughout your transaction.'],
        ];
        foreach ($why_us as $item): ?>
        <div class="text-center">
            <div class="w-16 h-16 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i data-lucide="<?= htmlspecialchars($item['icon'], ENT_QUOTES, 'UTF-8') ?>" class="w-8 h-8 text-emerald-600" aria-hidden="true"></i>
            </div>
            <h3 class="text-xs md:text-base lg:text-xl font-semibold text-gray-800 mb-2"><?= htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8') ?></h3>
            <!-- <p class="text-gray-600"><?= htmlspecialchars($item['desc'], ENT_QUOTES, 'UTF-8') ?></p> -->
        </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- Quick Contact CTA Banner -->
<section class="container mx-auto px-4 py-8 mb-8">
    <div class="bg-emerald-600 rounded-2xl p-8 md:p-12 text-center text-white">
        <h2 class="text-2xl md:text-3xl font-bold mb-4">Ready to Find Your Dream Property?</h2>
        <p class="text-lg text-white/90 mb-6 max-w-xl mx-auto">Contact us now to schedule a viewing or get expert advice. We respond within the hour.</p>
        <div class="flex flex-col sm:flex-row justify-center gap-4">
            <a href="https://wa.me/<?= CONTACT_PHONE_ENG ?>?text=Hello!%20I'd%20like%20to%20inquire%20about%20properties."
               target="_blank" rel="noopener noreferrer"
               class="flex items-center justify-center bg-white text-emerald-700 hover:bg-emerald-50 px-6 py-3 rounded-lg font-semibold transition-colors duration-200">
                <img src="img/Logos/whatsapp.png" alt="" class="w-5 h-5 mr-2" aria-hidden="true">
                WhatsApp English
            </a>
            <a href="<?= BASE_URL ?>listings"
               class="flex items-center justify-center border-2 border-white text-white hover:bg-white/10 px-6 py-3 rounded-lg font-semibold transition-colors duration-200">
                View All Properties
            </a>
        </div>
    </div>
</section>

</main>
<?php require_once __DIR__ . '/includes/navbar.php'; ?>
<?php require_once __DIR__ . '/includes/scroll-top.php'; ?>
<?php require_once __DIR__ . '/includes/footer.php'; ?>

<?php qmx_foot(); ?>