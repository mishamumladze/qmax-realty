<?php
declare(strict_types=1);

/**
 * QMAX Realty — Property Detail Template
 *
 * Renders a single property from config/properties.php (keyed by slug).
 * Each array key is defensively defaulted so one template covers every entry
 * even where optional keys are absent. Placeholder image placeholder_1.webp is the
 * fallback (no real photography yet).
 *
 * Expects (set by properties/details.php): $property, $all_properties,
 * $page_title, $page_desc.
 */

$fallback_img = BASE_URL . 'img/placeholder_1.webp';

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

<main id="swup" class="transition-fade min-h-screen bg-gray-50">

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
                    <div class="flex flex-wrap items-center gap-2 mb-3">
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

                    <!-- Quick stats chips -->
                    <?php if ($bedrooms !== null || $bathrooms !== null || $sqft !== null): ?>
                        <div class="flex flex-wrap gap-2 mt-4">
                            <?php if ($bedrooms !== null): ?>
                                <span class="inline-flex items-center gap-1.5 bg-gray-100 text-gray-700 text-sm font-medium px-3 py-1.5 rounded-full">
                                    <i data-lucide="bed" class="w-4 h-4 text-emerald-600" aria-hidden="true"></i>
                                    <?= $e($bedrooms) ?> Bed<?= $bedrooms == 1 ? '' : 's' ?>
                                </span>
                            <?php endif; ?>
                            <?php if ($bathrooms !== null): ?>
                                <span class="inline-flex items-center gap-1.5 bg-gray-100 text-gray-700 text-sm font-medium px-3 py-1.5 rounded-full">
                                    <i data-lucide="bath" class="w-4 h-4 text-emerald-600" aria-hidden="true"></i>
                                    <?= $e($bathrooms) ?> Bath<?= $bathrooms == 1 ? '' : 's' ?>
                                </span>
                            <?php endif; ?>
                            <?php if ($sqft !== null): ?>
                                <span class="inline-flex items-center gap-1.5 bg-gray-100 text-gray-700 text-sm font-medium px-3 py-1.5 rounded-full">
                                    <i data-lucide="square" class="w-4 h-4 text-emerald-600" aria-hidden="true"></i>
                                    <?= number_format((int)$sqft) ?> ft²
                                </span>
                            <?php endif; ?>
                            <?php if ($propertyType): ?>
                                <span class="inline-flex items-center gap-1.5 bg-gray-100 text-gray-700 text-sm font-medium px-3 py-1.5 rounded-full">
                                    <i data-lucide="building-2" class="w-4 h-4 text-emerald-600" aria-hidden="true"></i>
                                    <?= $e(ucfirst($propertyType)) ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="text-right">
                    <p class="text-xs uppercase tracking-wide text-gray-500 mb-1">Asking Price</p>
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
            <div id="property-gallery" class="lg:col-span-2">
                <div class="relative rounded-2xl overflow-hidden shadow-lg bg-gray-100">
                    <img id="gallery-out" src="<?= $e($gallery[0] ?? $cardImage) ?>" alt=""
                         class="absolute inset-0 w-full h-full object-cover" aria-hidden="true">
                    <img id="gallery-main" src="<?= $e($gallery[0] ?? $cardImage) ?>"
                         alt="<?= $e($altTexts[0] ?? $title) ?>"
                         class="relative w-full h-72 md:h-[480px] object-cover cursor-zoom-in"
                         loading="eager">
                    <button type="button" id="gallery-prev" aria-label="Previous image"
                            class="absolute left-3 top-1/2 -translate-y-1/2 bg-white/90 hover:bg-white text-gray-800 rounded-full p-2 shadow-md transition-colors duration-200">
                        <i data-lucide="chevron-left" class="w-5 h-5" aria-hidden="true"></i>
                    </button>
                    <button type="button" id="gallery-next" aria-label="Next image"
                            class="absolute right-3 top-1/2 -translate-y-1/2 bg-white/90 hover:bg-white text-gray-800 rounded-full p-2 shadow-md transition-colors duration-200">
                        <i data-lucide="chevron-right" class="w-5 h-5" aria-hidden="true"></i>
                    </button>
                    <span id="gallery-counter" class="absolute bottom-3 right-3 bg-black/60 text-white text-xs font-semibold px-3 py-1 rounded-full">1 / <?= count($gallery) ?></span>
                    <span class="absolute bottom-3 left-3 bg-black/60 text-white text-xs font-medium px-3 py-1 rounded-full inline-flex items-center gap-1.5">
                        <i data-lucide="maximize" class="w-3.5 h-3.5" aria-hidden="true"></i> Click to enlarge
                    </span>
                </div>

                <?php if (count($gallery) > 1): ?>
                <div id="gallery-thumbs" class="flex gap-3 mt-4 overflow-x-auto scrollbar-hide pb-1">
                    <?php foreach ($gallery as $gi => $img): ?>
                        <button type="button" data-gallery-thumb="<?= $gi ?>"
                                aria-label="View image <?= $gi + 1 ?>"
                                class="gallery-thumb flex-shrink-0 rounded-xl overflow-hidden border-2 transition-colors duration-200 <?= $gi === 0 ? 'border-emerald-600' : 'border-transparent hover:border-emerald-300' ?>">
                            <img src="<?= $e($img) ?>" alt="<?= $e($altTexts[$gi] ?? $title) ?>"
                                 class="w-24 h-16 md:w-28 md:h-20 object-cover" loading="lazy">
                        </button>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <div class="container mx-auto px-4 pb-16 grid grid-cols-1 lg:grid-cols-3 gap-8">

        <!-- Main column -->
        <div class="lg:col-span-2 space-y-8">

            <!-- Specs -->
            <div class="bg-white rounded-2xl shadow-sm p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-5 flex items-center gap-2">
                    <i data-lucide="gauge" class="w-5 h-5 text-emerald-600" aria-hidden="true"></i>
                    Key Facts
                </h2>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                    <?php if ($bedrooms !== null): ?>
                        <div class="flex flex-col items-center justify-center bg-gray-50 rounded-xl p-4 text-center">
                            <i data-lucide="bed" class="w-6 h-6 text-emerald-600 mb-1.5" aria-hidden="true"></i>
                            <span class="text-xl font-bold text-gray-900"><?= $e($bedrooms) ?></span>
                            <span class="text-xs text-gray-500">Bed<?= $bedrooms == 1 ? '' : 's' ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if ($bathrooms !== null): ?>
                        <div class="flex flex-col items-center justify-center bg-gray-50 rounded-xl p-4 text-center">
                            <i data-lucide="bath" class="w-6 h-6 text-emerald-600 mb-1.5" aria-hidden="true"></i>
                            <span class="text-xl font-bold text-gray-900"><?= $e($bathrooms) ?></span>
                            <span class="text-xs text-gray-500">Bath<?= $bathrooms == 1 ? '' : 's' ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if ($sqft !== null): ?>
                        <div class="flex flex-col items-center justify-center bg-gray-50 rounded-xl p-4 text-center">
                            <i data-lucide="square" class="w-6 h-6 text-emerald-600 mb-1.5" aria-hidden="true"></i>
                            <span class="text-xl font-bold text-gray-900"><?= number_format((int)$sqft) ?></span>
                            <span class="text-xs text-gray-500">ft²</span>
                        </div>
                    <?php endif; ?>
                    <?php if ($propertySize): ?>
                        <div class="flex flex-col items-center justify-center bg-gray-50 rounded-xl p-4 text-center">
                            <i data-lucide="ruler" class="w-6 h-6 text-emerald-600 mb-1.5" aria-hidden="true"></i>
                            <span class="text-xl font-bold text-gray-900 truncate max-w-full"><?= $e($propertySize) ?></span>
                            <span class="text-xs text-gray-500">Size</span>
                        </div>
                    <?php endif; ?>
                    <?php if ($floor !== ''): ?>
                        <div class="flex flex-col items-center justify-center bg-gray-50 rounded-xl p-4 text-center">
                            <i data-lucide="layers" class="w-6 h-6 text-emerald-600 mb-1.5" aria-hidden="true"></i>
                            <span class="text-xl font-bold text-gray-900 truncate max-w-full"><?= $e($floor) ?></span>
                            <span class="text-xs text-gray-500">Floor</span>
                        </div>
                    <?php endif; ?>
                    <?php if ($yearBuilt): ?>
                        <div class="flex flex-col items-center justify-center bg-gray-50 rounded-xl p-4 text-center">
                            <i data-lucide="calendar" class="w-6 h-6 text-emerald-600 mb-1.5" aria-hidden="true"></i>
                            <span class="text-xl font-bold text-gray-900"><?= $e($yearBuilt) ?></span>
                            <span class="text-xs text-gray-500">Built</span>
                        </div>
                    <?php endif; ?>
                    <?php if ($lotSize && $lotSize !== 'N/A'): ?>
                        <div class="flex flex-col items-center justify-center bg-gray-50 rounded-xl p-4 text-center">
                            <i data-lucide="trees" class="w-6 h-6 text-emerald-600 mb-1.5" aria-hidden="true"></i>
                            <span class="text-xl font-bold text-gray-900 truncate max-w-full"><?= $e($lotSize) ?></span>
                            <span class="text-xs text-gray-500">Lot</span>
                        </div>
                    <?php endif; ?>
                    <?php if ($parking): ?>
                        <div class="flex flex-col items-center justify-center bg-gray-50 rounded-xl p-4 text-center">
                            <i data-lucide="car" class="w-6 h-6 text-emerald-600 mb-1.5" aria-hidden="true"></i>
                            <span class="text-xl font-bold text-gray-900">Yes</span>
                            <span class="text-xs text-gray-500">Parking</span>
                        </div>
                    <?php endif; ?>
                </div>
                <?php if ($parking): ?>
                    <p class="text-sm text-gray-500 mt-4"><?= $e($parking) ?></p>
                <?php endif; ?>
            </div>

            <!-- Description -->
            <div class="bg-white rounded-2xl shadow-sm p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center gap-2">
                    <i data-lucide="info" class="w-5 h-5 text-emerald-600" aria-hidden="true"></i>
                    About This Property
                </h2>
                <div class="text-gray-700 leading-relaxed">
                    <?= $p['full_description'] ?? '' ?>
                </div>
            </div>

            <!-- Features -->
            <?php if (count($features)): ?>
                <div class="bg-white rounded-2xl shadow-sm p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <i data-lucide="sparkles" class="w-5 h-5 text-emerald-600" aria-hidden="true"></i>
                        Features &amp; Amenities
                    </h2>
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
                <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center gap-2">
                    <i data-lucide="map-pin" class="w-5 h-5 text-emerald-600" aria-hidden="true"></i>
                    Location &amp; Transport
                </h2>
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
                <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center gap-2">
                    <i data-lucide="layout-panel-left" class="w-5 h-5 text-emerald-600" aria-hidden="true"></i>
                    Floor Plan
                </h2>
                <img src="<?= $e($floorPlan) ?>" alt="Floor plan of <?= $e($title) ?>"
                     class="w-full h-auto rounded-xl" loading="lazy">
            </div>

            <!-- Virtual tour -->
            <?php if ($virtualUrl): ?>
                <div class="bg-white rounded-2xl shadow-sm p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <i data-lucide="video" class="w-5 h-5 text-emerald-600" aria-hidden="true"></i>
                        Virtual Tour
                    </h2>
                    <div class="aspect-video rounded-xl overflow-hidden">
                        <iframe title="Virtual tour" class="w-full h-full border-0" src="<?= $e($virtualUrl) ?>" loading="lazy" allow="fullscreen"></iframe>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Reviews -->
            <?php if (count($reviews)): ?>
                <div class="bg-white rounded-2xl shadow-sm p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <i data-lucide="star" class="w-5 h-5 text-emerald-600" aria-hidden="true"></i>
                        What People Say
                    </h2>
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
                    <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <i data-lucide="help-circle" class="w-5 h-5 text-emerald-600" aria-hidden="true"></i>
                        Frequently Asked Questions
                    </h2>
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
            <!-- Listing summary card -->
            <div class="bg-white rounded-2xl shadow-sm overflow-hidden lg:sticky lg:top-24">
                <img src="<?= $e($cardImage) ?>" alt="<?= $e($title) ?>"
                     class="w-full h-44 object-cover" loading="lazy">
                <div class="p-6">
                    <p class="text-xs uppercase tracking-wide text-gray-500 mb-1">Asking Price</p>
                    <p class="text-2xl font-black text-emerald-600 mb-1"><?= $e($currency) ?> <?= number_format($price, 0) ?></p>
                    <?php if (!empty($p['price_per_sqft'])): ?>
                        <p class="text-sm text-gray-500 mb-4"><?= $e($currency) ?> <?= (int)$p['price_per_sqft'] ?>/sq ft</p>
                    <?php else: ?>
                        <p class="text-sm text-gray-500 mb-4">&nbsp;</p>
                    <?php endif; ?>

                    <div class="border-t border-gray-100 pt-4 mb-4">
                        <p class="text-sm text-gray-600 mb-2 flex items-center gap-1.5">
                            <i data-lucide="map-pin" class="w-4 h-4 text-emerald-600 flex-shrink-0" aria-hidden="true"></i>
                            <span class="truncate"><?= $e($location ?: $title) ?></span>
                        </p>
                        <div class="flex flex-wrap gap-2 text-xs text-gray-600">
                            <?php if ($status): ?>
                                <span class="bg-emerald-50 text-emerald-700 font-medium px-2.5 py-1 rounded-full"><?= $e($status) ?></span>
                            <?php endif; ?>
                            <?php if ($propertyType): ?>
                                <span class="bg-blue-50 text-blue-700 font-medium px-2.5 py-1 rounded-full"><?= $e(ucfirst($propertyType)) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <p class="text-sm text-gray-600 mb-4">Enquire about this listing — we'll get back to you shortly.</p>

                    <div class="space-y-2 text-sm">
                        <a href="https://wa.me/<?= CONTACT_PHONE ?>?text=Hi!%20I'm%20interested%20in%20the%20<?= rawurlencode($title) ?>%20property."
                           target="_blank" rel="noopener noreferrer"
                           class="flex items-center justify-center gap-2 bg-green-500 hover:bg-green-600 text-white w-full px-4 py-3 rounded-xl font-semibold transition-colors duration-200">
                            <img src="<?= BASE_URL ?>img/Logos/si-whatsapp-w.svg" alt="" class="w-5 h-5"> WhatsApp
                        </a>
                        <a href="tel:<?= CONTACT_PHONE ?>"
                           class="flex items-center gap-2 border border-emerald-600 text-emerald-700 hover:bg-emerald-50 justify-center px-4 py-3 rounded-xl font-semibold transition-colors duration-200 w-full">
                            <i data-lucide="phone" class="w-4 h-4" aria-hidden="true"></i> Call Us
                        </a>
                        <a href="<?= BASE_URL ?>contact"
                           class="flex items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-3 rounded-xl font-semibold transition-colors duration-200">
                            <i data-lucide="mail" class="w-4 h-4" aria-hidden="true"></i> Contact Us
                        </a>
                    </div>
                </div>
            </div>

            <!-- Prev / next -->
            <div class="bg-white rounded-2xl shadow-sm p-6">
                <h3 class="font-bold text-gray-900 mb-3 flex items-center gap-2">
                    <i data-lucide="building" class="w-4 h-4 text-emerald-600" aria-hidden="true"></i>
                    More Properties
                </h3>
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

    <!-- Lightbox -->
    <div id="gallery-lightbox" class="fixed inset-0 z-[60] hidden flex items-center justify-center bg-black/90 backdrop-blur-sm">
        <button type="button" id="lightbox-close" aria-label="Close gallery"
                class="absolute top-4 right-4 text-white/90 hover:text-white bg-white/10 hover:bg-white/20 rounded-full p-2 transition-colors duration-200">
            <i data-lucide="x" class="w-6 h-6" aria-hidden="true"></i>
        </button>
        <button type="button" id="lightbox-prev" aria-label="Previous image"
                class="absolute left-4 top-1/2 -translate-y-1/2 text-white/90 hover:text-white bg-white/10 hover:bg-white/20 rounded-full p-3 transition-colors duration-200">
            <i data-lucide="chevron-left" class="w-6 h-6" aria-hidden="true"></i>
        </button>
        <div id="lightbox-stage" class="relative flex items-center justify-center w-full max-h-[85vh] overflow-hidden rounded-lg shadow-2xl">
            <img id="lightbox-out" src="" alt=""
                 class="absolute inset-0 w-full h-full object-contain" aria-hidden="true">
            <img id="lightbox-img" src="" alt=""
                 class="relative max-h-[85vh] w-full object-contain">
        </div>
        <button type="button" id="lightbox-next" aria-label="Next image"
                class="absolute right-4 top-1/2 -translate-y-1/2 text-white/90 hover:text-white bg-white/10 hover:bg-white/20 rounded-full p-3 transition-colors duration-200">
            <i data-lucide="chevron-right" class="w-6 h-6" aria-hidden="true"></i>
        </button>
        <span id="lightbox-counter" class="absolute bottom-6 left-1/2 -translate-x-1/2 text-white/90 text-sm font-medium bg-white/10 rounded-full px-4 py-1.5"></span>
        <div id="lightbox-thumbs" class="absolute bottom-16 left-1/2 -translate-x-1/2 flex items-center gap-2 max-w-[90vw] overflow-x-auto scrollbar-hide bg-black/50 rounded-2xl px-3 py-2"></div>
    </div>
</main>

<?php
require __DIR__ . '/navbar.php';
require __DIR__ . '/scroll-top.php';
require __DIR__ . '/footer.php';
qmx_foot();