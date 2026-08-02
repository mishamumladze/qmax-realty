<?php
declare(strict_types=1);

/**
 * QMAX Realty — Property Detail Template
 *
 * Renders a single property from config/properties.php (keyed by slug).
 * Each array key is defensively defaulted so one template covers every entry
 * even where optional keys are absent. Placeholder image placeholder.webp is the
 * fallback (no real photography yet).
 *
 * Expects (set by properties/details.php): $property, $all_properties,
 * $page_title, $page_desc, $current_page.
 */

$current_page = $current_page ?? 'listings';
$fallback_img = BASE_URL . 'img/placeholder.webp';

// ── 404 path ────────────────────────────────────────────────────────────────
if (!is_array($property)) {
    qmx_head($page_title, $page_desc);
    ?>
    <main class="min-h-screen container mx-auto px-4 md:py-24 py-16 text-center">
        <h1 class="text-4xl md:text-5xl font-bold text-gray-800 mb-4">Property Not Found</h1>
        <p class="text-gray-600 max-w-md mx-auto mb-8">The property you are looking for could not be found. Browse our premium real estate listings instead.</p>
        <a href="<?= BASE_URL ?>listings"
           class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white px-8 py-3 rounded-xl font-semibold transition-colors duration-200">
            <i data-lucide="home" class="w-5 h-5" aria-hidden="true"></i>
            Browse All Properties
        </a>
    </main>
    <?php
    require __DIR__ . '/navbar.php';
    require __DIR__ . '/scroll-top.php';
    require __DIR__ . '/footer.php';
    qmx_foot();
    return;
}

$p = $property;

$e = static fn(mixed $v): string => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');

$title        = $p['title'] ?? '';
$subtitle     = $p['subtitle'] ?? '';
$price        = (int)($p['price'] ?? 0);
$currency     = $p['currency'] ?? 'USD';
$status       = $p['status'] ?? '';
$location     = $p['location'] ?? '';
$propertyType = $p['property_type'] ?? ($p['type'] ?? '');
$bedrooms     = $p['bedrooms'] ?? null;
$bathrooms    = $p['bathrooms'] ?? null;
$sqft         = $p['sqft'] ?? null;
$propertySize = $p['property_size'] ?? (($sqft !== null) ? $sqft . ' sq ft' : '');
$floor        = $p['floor'] ?? '';
$yearBuilt    = $p['year_built'] ?? '';
$lotSize      = $p['lot_size'] ?? '';
$parking      = $p['parking'] ?? '';

$cardImage  = $p['card_image'] ?: $fallback_img;
$gallery    = (is_array($p['gallery'] ?? null) && count($p['gallery'])) ? array_values($p['gallery']) : [$cardImage];
$altTexts   = is_array($p['alt_texts'] ?? null) ? array_values($p['alt_texts']) : [];
$features   = $p['features'] ?? [];
$inclusions = $p['inclusions'] ?? [];
$exclusions = $p['exclusions'] ?? [];
$knowBefore = $p['know_before_you_go'] ?? [];
$whatToBring= $p['what_to_bring'] ?? [];
$floorPlan  = ($p['floor_plan'] ?? '') ?: $fallback_img;
$virtualUrl = $p['virtual_tour'] ?? '';
$coords     = $p['coords'] ?? [];
$nearby     = $p['nearby'] ?? [];
$transport  = $p['transport'] ?? [];
$reviews    = $p['reviews'] ?? [];
$faq        = $p['faq'] ?? [];

$slug = $p['slug'] ?? '';

// Prev / next by array order
$keys = array_keys($all_properties);
$idx  = array_search($slug, $keys, true);
$prev = ($idx !== false && isset($keys[$idx - 1])) ? $all_properties[$keys[$idx - 1]] : null;
$next = ($idx !== false && isset($keys[$idx + 1])) ? $all_properties[$keys[$idx + 1]] : null;

qmx_head($page_title, $page_desc, 'bg-white text-gray-800');
?>

<main class="min-h-screen bg-gray-50">

    <!-- Breadcrumb -->
    <div class="bg-white border-b border-gray-200">
        <div class="container mx-auto px-4 py-4 text-sm text-gray-500">
            <a href="<?= BASE_URL ?>" class="hover:text-emerald-600">Home</a>
            <span class="mx-2">/</span>
            <a href="<?= BASE_URL ?>listings" class="hover:text-emerald-600">Properties</a>
            <span class="mx-2">/</span>
            <span class="text-gray-800"><?= $e($title) ?></span>
        </div>
    </div>

    <!-- Header -->
    <section class="bg-white border-b border-gray-200">
        <div class="container mx-auto px-4 py-8 md:py-10">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="max-w-3xl">
                    <div class="flex flex-wrap items-center gap-2 mb-2">
                        <?php if ($propertyType): ?>
                            <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-2.5 py-1 rounded-full"><?= $e(ucfirst($propertyType)) ?></span>
                        <?php endif; ?>
                        <?php if ($status): ?>
                            <span class="bg-emerald-600 text-white text-xs font-semibold px-2.5 py-1 rounded-full"><?= $e($status) ?></span>
                        <?php endif; ?>
                    </div>
                    <h1 class="text-3xl md:text-4xl font-extrabold text-gray-900 mb-2"><?= $e($title) ?></h1>
                    <?php if ($subtitle): ?>
                        <p class="text-gray-500 text-lg"><?= $e($subtitle) ?></p>
                    <?php endif; ?>
                    <?php if ($location): ?>
                        <p class="mt-2 text-gray-600 flex items-center gap-1.5">
                            <i data-lucide="map-pin" class="w-4 h-4 text-emerald-600" aria-hidden="true"></i>
                            <?= $e($location) ?>
                        </p>
                    <?php endif; ?>
                </div>
                <div class="text-right">
                    <p class="text-3xl md:text-4xl font-black text-emerald-600"><?= $e($currency) ?> <?= number_format($price, 0) ?></p>
                    <?php if (!empty($p['price_per_sqft'])): ?>
                        <p class="text-gray-500 text-sm mt-1"><?= $e($currency) ?> <?= (int)$p['price_per_sqft'] ?>/sq ft</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Gallery -->
    <section class="container mx-auto px-4 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <img src="<?= $e($gallery[0] ?? $cardImage) ?>"
                 alt="<?= $e($altTexts[0] ?? $title) ?>"
                 class="w-full h-72 md:h-96 object-cover rounded-2xl shadow-lg" loading="eager">
            <div class="grid grid-cols-2 gap-4">
                <?php for ($i = 1; $i < min(5, count($gallery)); $i++): ?>
                    <img src="<?= $e($gallery[$i] ?? $cardImage) ?>"
                         alt="<?= $e($altTexts[$i] ?? $title) ?>"
                         class="w-full h-34 md:h-46 object-cover rounded-xl">
                <?php endfor; ?>
            </div>
        </div>
    </section>

    <div class="container mx-auto px-4 pb-16 grid grid-cols-1 lg:grid-cols-3 gap-8">

        <!-- Main column -->
        <div class="lg:col-span-2 space-y-8">

            <!-- Specs -->
            <div class="bg-white rounded-2xl shadow-sm p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Key Facts</h2>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4 text-sm">
                    <?php if ($bedrooms !== null): ?>
                        <div class="flex items-center gap-2 text-gray-700 text-xs"><i data-lucide="bed" class="w-4 h-4 text-emerald-600"></i> <span><?= $e($bedrooms) ?> Bed<?= $bedrooms == 1 ? '' : 's' ?></span></div>
                    <?php endif; ?>
                    <?php if ($bathrooms !== null): ?>
                        <div class="flex items-center gap-2 text-gray-700 text-xs"><i data-lucide="bath" class="w-4 h-4 text-emerald-600"></i> <span><?= $e($bathrooms) ?> Bath<?= $bathrooms == 1 ? '' : 's' ?></span></div>
                    <?php endif; ?>
                    <?php if ($sqft !== null): ?>
                        <div class="flex items-center gap-2 text-gray-700 text-xs"><i data-lucide="square" class="w-4 h-4 text-emerald-600"></i> <span><?= number_format((int)$sqft) ?> ft²</span></div>
                    <?php endif; ?>
                    <?php if ($propertySize): ?>
                        <div class="flex items-center gap-2 text-gray-700 text-xs"><i data-lucide="ruler" class="w-4 h-4 text-emerald-600"></i> <span><?= $e($propertySize) ?></span></div>
                    <?php endif; ?>
                    <?php if ($floor !== ''): ?>
                        <div class="flex items-center gap-2 text-gray-700 text-xs"><i data-lucide="layers" class="w-4 h-4 text-emerald-600"></i> <span><?= $e($floor) ?></span></div>
                    <?php endif; ?>
                    <?php if ($yearBuilt): ?>
                        <div class="flex items-center gap-2 text-gray-700 text-xs"><i data-lucide="calendar" class="w-4 h-4 text-emerald-600"></i> <span>Built <?= $e($yearBuilt) ?></span></div>
                    <?php endif; ?>
                    <?php if ($lotSize && $lotSize !== 'N/A'): ?>
                        <div class="flex items-center gap-2 text-gray-700 text-xs"><i data-lucide="trees" class="w-4 h-4 text-emerald-600"></i> <span>Lot <?= $e($lotSize) ?></span></div>
                    <?php endif; ?>
                    <?php if ($parking): ?>
                        <div class="flex items-center gap-2 text-gray-700 text-xs"><i data-lucide="car" class="w-4 h-4 text-emerald-600"></i> <span>Parking</span></div>
                    <?php endif; ?>
                </div>
                <?php if ($parking): ?>
                    <p class="text-sm text-gray-500 mt-3"><?= $e($parking) ?></p>
                <?php endif; ?>
            </div>

            <!-- Description -->
            <div class="bg-white rounded-2xl shadow-sm p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">About This Property</h2>
                <div class="text-gray-700 leading-relaxed">
                    <?= $p['full_description'] ?? '' ?>
                </div>
            </div>

            <!-- Features -->
            <?php if (count($features)): ?>
                <div class="bg-white rounded-2xl shadow-sm p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Features &amp; Amenities</h2>
                    <ul class="grid grid-cols-1 md:grid-cols-2 gap-2 list-none">
                        <?php foreach ($features as $f): ?>
                            <li class="flex items-start gap-2 text-gray-700 text-sm"><i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600 flex-shrink-0 mt-0.5"></i> <span><?= $e($f) ?></span></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- Inclusions / exclusions -->
            <?php if (count($inclusions) || count($exclusions)): ?>
                <div class="bg-white rounded-2xl shadow-sm p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <?php if (count($inclusions)): ?>
                            <div>
                                <h3 class="font-bold text-gray-900 mb-3">Included</h3>
                                <ul class="space-y-2 text-gray-700 text-sm">
                                    <?php foreach ($inclusions as $item): ?><li><?= $e($item) ?></li><?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        <?php if (count($exclusions)): ?>
                            <div>
                                <h3 class="font-bold text-gray-900 mb-3">Not Included</h3>
                                <ul class="space-y-2 text-gray-700 text-sm">
                                    <?php foreach ($exclusions as $item): ?><li><?= $e($item) ?></li><?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Know before / what to bring -->
            <?php if (count($knowBefore) || count($whatToBring)): ?>
                <div class="bg-white rounded-2xl shadow-sm p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <?php if (count($knowBefore)): ?>
                            <div>
                                <h3 class="font-bold text-gray-900 mb-3">Good to Know</h3>
                                <ul class="space-y-2 text-gray-700 text-sm">
                                    <?php foreach ($knowBefore as $item): ?><li><?= $e($item) ?></li><?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        <?php if (count($whatToBring)): ?>
                            <div>
                                <h3 class="font-bold text-gray-900 mb-3">Viewing Requirements</h3>
                                <ul class="space-y-2 text-gray-700 text-sm">
                                    <?php foreach ($whatToBring as $item): ?><li><?= $e($item) ?></li><?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Location & transport -->
            <div class="bg-white rounded-2xl shadow-sm p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Location &amp; Transport</h2>
                <?php if (!empty($coords['lat'])): ?>
                    <div class="rounded-xl overflow-hidden mb-4">
                        <iframe
                            title="Property map"
                            class="w-full h-72 border-0"
                            loading="lazy"
                            src="https://www.google.com/maps?q=<?= (float)$coords['lat'] ?>,<?= (float)$coords['lng'] ?>&hl=en&z=15&output=embed">
                        </iframe>
                    </div>
                <?php endif; ?>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <?php if (count($nearby)): ?>
                        <div>
                            <h3 class="font-bold text-gray-900 mb-3">Nearby</h3>
                            <ul class="space-y-2 text-gray-700 text-sm">
                                <?php foreach ($nearby as $item): ?><li><?= $e($item) ?></li><?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    <?php if (count($transport)): ?>
                        <div>
                            <h3 class="font-bold text-gray-900 mb-3">Transport</h3>
                            <ul class="space-y-2 text-gray-700 text-sm">
                                <?php foreach ($transport as $item): ?><li><?= $e($item) ?></li><?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Floor plan -->
            <div class="bg-white rounded-2xl shadow-sm p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Floor Plan</h2>
                <img src="<?= $e($floorPlan) ?>" alt="Floor plan of <?= $e($title) ?>"
                     class="w-full h-auto rounded-xl" loading="lazy">
            </div>

            <!-- Virtual tour -->
            <?php if ($virtualUrl): ?>
                <div class="bg-white rounded-2xl shadow-sm p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Virtual Tour</h2>
                    <div class="aspect-video rounded-xl overflow-hidden">
                        <iframe title="Virtual tour" class="w-full h-full border-0" src="<?= $e($virtualUrl) ?>" loading="lazy" allow="fullscreen"></iframe>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Reviews -->
            <?php if (count($reviews)): ?>
                <div class="bg-white rounded-2xl shadow-sm p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">What People Say</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <?php foreach ($reviews as $r): ?>
                            <div class="bg-gray-50 rounded-xl p-4">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="font-semibold text-gray-800"><?= $e($r['author'] ?? '') ?></span>
                                    <span class="text-xs text-gray-400"><?= $e($r['platform'] ?? '') ?></span>
                                </div>
                                <div class="flex gap-0.5 mb-2" aria-label="Rating <?= $e($r['rating'] ?? '') ?>">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i data-lucide="star" class="w-4 h-4 <?= ($i <= round((float)($r['rating'] ?? 0))) ? 'text-yellow-400 fill-yellow-400' : 'text-gray-300' ?>"></i>
                                    <?php endfor; ?>
                                </div>
                                <p class="text-sm text-gray-600 leading-relaxed"><?= $e($r['text'] ?? '') ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- FAQ -->
            <?php if (count($faq)): ?>
                <div class="bg-white rounded-2xl shadow-sm p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Frequently Asked Questions</h2>
                    <div class="space-y-3">
                        <?php foreach ($faq as $i => $fq): ?>
                            <details class="group border border-gray-200 rounded-xl p-4">
                                <summary class="flex items-center justify-between cursor-pointer font-medium text-gray-800 list-none">
                                    <?= $e($fq['question'] ?? '') ?>
                                    <i data-lucide="chevron-down" class="w-4 h-4 text-gray-400 group-open:rotate-180 transition-transform"></i>
                                </summary>
                                <p class="mt-3 text-sm text-gray-600 leading-relaxed"><?= $e($fq['answer'] ?? '') ?></p>
                            </details>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

        </div>

        <!-- Sidebar -->
        <aside class="space-y-6">
            <!-- Contact card -->
            <div class="bg-white rounded-2xl shadow-sm p-6 lg:sticky lg:top-24">
                <h2 class="text-lg font-bold text-gray-900 mb-4">Enquire About This Property</h2>

                <p class="text-sm text-gray-600 mb-4">Send us a message and we'll get back to you shortly about this listing.</p>

                <div class="space-y-2 text-sm">
                    <a href="https://wa.me/<?= CONTACT_PHONE_ENG ?>?text=Hi!%20I'm%20interested%20in%20the%20<?= rawurlencode($title) ?>%20property."
                       target="_blank" rel="noopener noreferrer"
                       class="flex items-center justify-center gap-2 bg-green-500 hover:bg-green-600 text-white w-full px-4 py-3 rounded-xl font-semibold transition-colors duration-200">
                        <img src="<?= BASE_URL ?>img/Logos/whatsapp.png" alt="" class="w-5 h-5"> WhatsApp
                    </a>
                    <a href="tel:<?= CONTACT_PHONE_ENG ?>"
                       class="flex items-center gap-2 border border-emerald-600 text-emerald-700 hover:bg-emerald-50 justify-center px-4 py-3 rounded-xl font-semibold transition-colors duration-200 w-full">
                        <i data-lucide="phone" class="w-4 h-4"></i> Call Us
                    </a>
                    <a href="<?= BASE_URL ?>contact"
                       class="flex items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-3 rounded-xl font-semibold transition-colors duration-200">
                        <i data-lucide="mail" class="w-4 h-4"></i> Contact Us
                    </a>
                </div>
            </div>

            <!-- Prev / next -->
            <div class="bg-white rounded-2xl shadow-sm p-6">
                <h3 class="font-bold text-gray-900 mb-3">More Properties</h3>
                <div class="grid grid-cols-1 gap-3">
                    <?php if ($next): ?>
                        <a href="<?= BASE_URL ?>properties/details/<?= $e($next['slug']) ?>" class="flex items-center gap-3 hover:bg-gray-50 rounded-xl p-2 transition-colors">
                            <img src="<?= $e($next['card_image'] ?: $fallback_img) ?>" alt="" class="w-16 h-12 object-cover rounded-lg">
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-gray-800 truncate"><?= $e($next['title'] ?? '') ?></p>
                                <p class="text-xs text-emerald-600">$<?= number_format((int)($next['price'] ?? 0)) ?></p>
                            </div>
                        </a>
                    <?php endif; ?>
                    <?php if ($prev): ?>
                        <a href="<?= BASE_URL ?>properties/details/<?= $e($prev['slug']) ?>" class="flex items-center gap-3 hover:bg-gray-50 rounded-xl p-2 transition-colors">
                            <img src="<?= $e($prev['card_image'] ?: $fallback_img) ?>" alt="" class="w-16 h-12 object-cover rounded-lg">
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-gray-800 truncate"><?= $e($prev['title'] ?? '') ?></p>
                                <p class="text-xs text-emerald-600">$<?= number_format((int)($prev['price'] ?? 0)) ?></p>
                            </div>
                        </a>
                    <?php endif; ?>
                    <?php if (!$next && !$prev): ?>
                        <p class="text-sm text-gray-500">No other properties available right now.</p>
                    <?php endif; ?>
                </div>
            </div>
        </aside>

    </div>
</main>

<?php
require __DIR__ . '/navbar.php';
require __DIR__ . '/scroll-top.php';
require __DIR__ . '/footer.php';
qmx_foot();