<?php
    declare(strict_types=1);
    session_start(); // Add this!
    $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
    require_once __DIR__ . '/includes/layout.php';

    qmx_head(
        'Properties for Sale & Rent in Georgia — QMAX Realty',
        'Browse all properties for sale and rent in Georgia. Apartments, houses, commercial real estate and more.'
    );

    $all_properties = require __DIR__ . '/config/properties.php';

    // Counts per type for filter badge display
    $counts = ['all' => 0, 'apartment' => 0, 'house' => 0];
    foreach ($all_properties as $p) {
        $counts['all']++;
        $type = $p['type'] ?? 'apartment';
        if (isset($counts[$type])) $counts[$type]++;
    }

    // Unique countries & cities for the modal's smart selects.
    // Derived from live data so future properties appear automatically.
    $geo_countries = [];
    $geo_cities    = [];
    foreach ($all_properties as $p) {
        $c = $p['country'] ?? 'Georgia';
        $ci = $p['city'] ?? '';
        if ($c  !== '') $geo_countries[$c]  = true;
        if ($ci !== '') $geo_cities[$ci]    = true;
    }
    $geo_countries = array_keys($geo_countries); sort($geo_countries, SORT_STRING);
    $geo_cities    = array_keys($geo_cities);    sort($geo_cities,    SORT_STRING);

    // Read ?filter= from URL
    $valid_filters  = ['all', 'apartment', 'house'];
    $initial_filter = in_array($_GET['filter'] ?? '', $valid_filters, true)
        ? $_GET['filter']
        : 'all';
?>

<main id="swup" class="transition-fade min-h-screen container mx-auto px-4 md:py-18 py-0">

    <!-- Hero Banner -->
    <header class="relative bg-gradient-to-r from-emerald-600 to-emerald-700 text-white py-14 md:py-20">
        <div class="absolute inset-0 bg-black/20" aria-hidden="true"></div>
        <div class="relative container mx-auto px-4 text-center">
            <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold mb-4">Properties & Listings</h1>
            <p class="text-lg md:text-xl mb-8 max-w-2xl mx-auto opacity-90">
                Buy, sell, or rent properties across Georgia.
            </p>
            <div class="max-w-xl mx-auto relative">
                <label for="property-search" class="sr-only">Search properties</label>
                <div class="relative">
                    <i data-lucide="search" class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-emerald-400 pointer-events-none" aria-hidden="true"></i>
                    <input
                        id="property-search"
                        type="search"
                        placeholder="Search by title, location, neighborhood…"
                        autocomplete="off"
                        class="w-full pl-12 pr-4 py-3.5 rounded-xl text-gray-800 text-base shadow-lg focus:outline-none focus:ring-2 focus:ring-emerald-300 bg-white/95"
                        aria-label="Search properties">
                </div>
            </div>
        </div>
    </header>

    <!-- Filter & Sort Bar -->
    <div class="sticky top-0 md:top-16 bg-white border-b border-gray-200 shadow-sm">
        <div class="container mx-auto px-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 py-3">

                <!-- Type Filter Tabs -->
                <div class="flex gap-2 overflow-x-auto pb-1 sm:pb-0 scrollbar-hide" role="tablist" aria-label="Filter by property type">
                    <?php
                    $filters = [
                        'all'        => ['label' => 'All',           'icon' => 'layout-grid'],
                        'apartment'  => ['label' => 'Apartments',    'icon' => 'building-2'],
                        'house'      => ['label' => 'Houses',        'icon' => 'home'],
                    ];
                    foreach ($filters as $key => $f):
                        $is_active = ($key === $initial_filter);
                    ?>
                    <button
                        class="cursor-pointer filter-tab flex items-center gap-1.5 px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap transition-all duration-200 border <?= $is_active ? 'active' : '' ?>"
                        data-filter="<?= $key ?>"
                        role="tab"
                        aria-selected="<?= $is_active ? 'true' : 'false' ?>">
                        <i data-lucide="<?= $f['icon'] ?>" class="w-4 h-4" aria-hidden="true"></i>
                        <?= $f['label'] ?>
                        <span class="ml-1 bg-gray-100 text-gray-600 text-xs font-semibold px-1.5 py-0.5 rounded-full count-badge" data-type="<?= $key ?>">
                            <?= $counts[$key] ?>
                        </span>
                    </button>
                    <?php endforeach; ?>
                </div>

                <!-- Sort + Filters -->
                <div class="flex items-center gap-2 flex-shrink-0">
                    <button id="flt-open-btn" type="button"
                        class="relative inline-flex items-center gap-1.5 px-4 py-2 rounded-full text-sm font-semibold border border-emerald-600 text-emerald-700 bg-emerald-50 hover:bg-emerald-100 transition-colors duration-200 cursor-pointer">
                        <i data-lucide="sliders-horizontal" class="w-4 h-4" aria-hidden="true"></i>
                        Filters
                        <span id="flt-count-badge" hidden class="absolute -top-1.5 -right-1.5 min-w-[1.25rem] h-5 px-1 bg-emerald-600 text-white text-[11px] font-bold flex items-center justify-center rounded-full">0</span>
                    </button>
                    <label for="sort-select" class="text-sm text-gray-500 font-medium">Sort:</label>
                    <select id="sort-select" class="text-sm border border-gray-200 rounded-lg px-3 py-2 text-gray-700 bg-white focus:outline-none focus:ring-2 focus:ring-emerald-400">
                        <option value="default">Relevance</option>
                        <option value="price-asc">Price: Low &rarr; High</option>
                        <option value="price-desc">Price: High &rarr; Low</option>
                        <option value="sqft-asc">Size: Smallest</option>
                        <option value="sqft-desc">Size: Largest</option>
                    </select>
                </div>

            </div>

            <!-- Active Filters chips — inside the same Filter & Sort bar -->
            <div id="flt-active-chips" class="hidden border-t border-gray-200 py-2.5 flex flex-wrap items-center gap-2">
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Active:</span>
                <div id="flt-chips-list" class="flex flex-wrap items-center gap-2"></div>
                <button id="flt-clear-chips" type="button" class="text-xs font-medium text-emerald-600 hover:underline cursor-pointer">Clear all</button>
            </div>
        </div>
    </div>

    <style>
    .flt-pill{ display:inline-flex; align-items:center; gap:.5rem; border:1px solid #e5e7eb; background:#fff; color:#4b5563; padding:.5rem 1.1rem; border-radius:9999px; font-size:.875rem; font-weight:500; cursor:pointer; transition:all .2s ease; }
    .flt-pill:hover{ background:#f9fafb; }
    .flt-pill-active{ background:#059669; color:#fff; border-color:#059669; }
    .flt-pill svg{ width:1rem; height:1rem; }
    .flt-preset{ display:inline-flex; align-items:center; gap:.4rem; border:1px solid #e5e7eb; background:#fff; color:#047857; padding:.35rem .8rem; border-radius:9999px; font-size:.8125rem; font-weight:600; cursor:pointer; transition:all .2s ease; }
    .flt-preset:hover{ background:#ecfdf5; border-color:#a7f3d0; }
    .flt-preset-active{ background:#ecfdf5; border-color:#059669; color:#065f46; }
    .flt-chip{ display:inline-flex; align-items:center; gap:.4rem; background:#ecfdf5; color:#047857; border:1px solid #a7f3d0; padding:.2rem .65rem; border-radius:9999px; font-size:.8125rem; font-weight:600; }
    .flt-chip button{ color:#047857; cursor:pointer; font-size:.7rem; line-height:1; display:inline-flex; align-items:center; }
    .flt-chip button:hover{ color:#065f46; }
    </style>

    <!-- Filters Modal -->
    <div id="flt-modal" class="fixed inset-0 z-50 hidden overflow-y-auto" role="dialog" aria-modal="true" aria-label="Filter properties">
        <div id="flt-backdrop" class="fixed inset-0 bg-black/50"></div>
        <div class="relative min-h-full flex items-center justify-center p-4">
            <div class="relative w-full max-w-lg bg-white rounded-2xl shadow-2xl overflow-hidden">

                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center gap-2">
                        <i data-lucide="sliders-horizontal" class="w-5 h-5 text-emerald-600" aria-hidden="true"></i>
                        <h2 class="text-lg font-bold text-gray-800">Filter Properties</h2>
                    </div>
                    <button id="flt-close-btn" type="button" class="text-gray-400 hover:text-gray-600 cursor-pointer" aria-label="Close">
                        <i data-lucide="x" class="w-6 h-6"></i>
                    </button>
                </div>

                <div class="px-6 py-5 space-y-5 max-h-[60vh] overflow-y-auto">
                    <div>
                        <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2"><i data-lucide="tag" class="w-3.5 h-3.5 inline-block align-text-top mr-1 text-emerald-500" aria-hidden="true"></i>Availability</span>
                        <div id="flt-offer" class="flex flex-wrap gap-2">
                            <button type="button" class="flt-pill flt-pill-active" data-offer-pill="all"><i data-lucide="layout-grid" aria-hidden="true"></i>All</button>
                            <button type="button" class="flt-pill" data-offer-pill="sale"><i data-lucide="tag" aria-hidden="true"></i>For Sale</button>
                            <button type="button" class="flt-pill" data-offer-pill="rent"><i data-lucide="badge-check" aria-hidden="true"></i>For Rent</button>
                        </div>
                    </div>

                    <div>
                        <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2"><i data-lucide="layout-grid" class="w-3.5 h-3.5 inline-block align-text-top mr-1 text-emerald-500" aria-hidden="true"></i>Property Type</span>
                        <div id="flt-type" class="flex flex-wrap gap-2">
                            <button type="button" class="flt-pill flt-pill-active" data-type-pill="all"><i data-lucide="layout-grid" aria-hidden="true"></i>All</button>
                            <button type="button" class="flt-pill" data-type-pill="apartment"><i data-lucide="building-2" aria-hidden="true"></i>Apartments</button>
                            <button type="button" class="flt-pill" data-type-pill="house"><i data-lucide="home" aria-hidden="true"></i>Houses</button>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="flt-country" class="block text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2"><i data-lucide="globe" class="w-3.5 h-3.5 inline-block align-text-top mr-1 text-emerald-500" aria-hidden="true"></i>Country</label>
                            <select id="flt-country" class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2.5 text-gray-700 bg-white focus:outline-none focus:ring-2 focus:ring-emerald-400">
                                <option value="">All countries</option>
                                <?php foreach ($geo_countries as $gc): ?>
                                <option value="<?= htmlspecialchars($gc, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($gc, ENT_QUOTES, 'UTF-8') ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label for="flt-city" class="block text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2"><i data-lucide="map-pin" class="w-3.5 h-3.5 inline-block align-text-top mr-1 text-emerald-500" aria-hidden="true"></i>City / Neighborhood</label>
                            <select id="flt-city" class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2.5 text-gray-700 bg-white focus:outline-none focus:ring-2 focus:ring-emerald-400">
                                <option value="">All cities</option>
                                <?php foreach ($geo_cities as $gc): ?>
                                <option value="<?= htmlspecialchars($gc, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($gc, ENT_QUOTES, 'UTF-8') ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="flt-bedrooms" class="block text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2"><i data-lucide="bed-double" class="w-3.5 h-3.5 inline-block align-text-top mr-1 text-emerald-500" aria-hidden="true"></i>Bedrooms</label>
                            <select id="flt-bedrooms" class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2.5 text-gray-700 bg-white focus:outline-none focus:ring-2 focus:ring-emerald-400">
                                <option value="0">Any</option>
                                <option value="1">1+</option>
                                <option value="2">2+</option>
                                <option value="3">3+</option>
                                <option value="4">4+</option>
                            </select>
                        </div>
                        <div>
                            <label for="flt-bathrooms" class="block text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2"><i data-lucide="bath" class="w-3.5 h-3.5 inline-block align-text-top mr-1 text-emerald-500" aria-hidden="true"></i>Bathrooms</label>
                            <select id="flt-bathrooms" class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2.5 text-gray-700 bg-white focus:outline-none focus:ring-2 focus:ring-emerald-400">
                                <option value="0">Any</option>
                                <option value="1">1+</option>
                                <option value="2">2+</option>
                                <option value="3">3+</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2"><i data-lucide="circle-dollar-sign" class="w-3.5 h-3.5 inline-block align-text-top mr-1 text-emerald-500" aria-hidden="true"></i>Price Range (USD)</span>
                        <div id="flt-presets" class="flex flex-wrap gap-2 mb-3">
                            <button type="button" class="flt-preset" data-preset="u200"><i data-lucide="arrow-down" aria-hidden="true"></i>Under $200k</button>
                            <button type="button" class="flt-preset" data-preset="200-400"><i data-lucide="repeat" aria-hidden="true"></i>$200k – $400k</button>
                            <button type="button" class="flt-preset" data-preset="400-600"><i data-lucide="repeat" aria-hidden="true"></i>$400k – $600k</button>
                            <button type="button" class="flt-preset" data-preset="600p"><i data-lucide="arrow-up" aria-hidden="true"></i>Over $600k</button>
                        </div>
                        <div class="flex items-center gap-3">
                            <input type="number" id="flt-price-min" min="0" step="1000" placeholder="Min $"
                                class="w-1/2 text-sm border border-gray-200 rounded-lg px-3 py-2.5 text-gray-700 bg-white focus:outline-none focus:ring-2 focus:ring-emerald-400">
                            <span class="text-gray-400">–</span>
                            <input type="number" id="flt-price-max" min="0" step="1000" placeholder="Max $"
                                class="w-1/2 text-sm border border-gray-200 rounded-lg px-3 py-2.5 text-gray-700 bg-white focus:outline-none focus:ring-2 focus:ring-emerald-400">
                        </div>
                    </div>

                </div>

                <div class="flex items-center justify-between gap-3 px-6 py-4 bg-gray-50 border-t border-gray-200">
                    <button id="flt-clear-btn" type="button" class="inline-flex items-center gap-1.5 text-sm font-medium text-gray-500 hover:text-gray-700 cursor-pointer"><i data-lucide="rotate-ccw" class="w-4 h-4" aria-hidden="true"></i>Clear all</button>
                    <button id="flt-apply-btn" type="button" class="inline-flex items-center justify-center gap-1.5 flex-1 bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-2.5 rounded-lg font-semibold text-sm transition-colors duration-200 cursor-pointer"><i data-lucide="check" class="w-4 h-4" aria-hidden="true"></i>Apply filters</button>
                </div>

            </div>
        </div>
    </div>

    <!-- Results area -->
    <div class="py-10 md:py-14 bg-gray-50">
        <div class="container mx-auto px-4">

            <p class="text-sm text-gray-500 mb-6" id="results-count" aria-live="polite">
                Showing <span id="visible-count"><?= count($all_properties) ?></span> of <?= count($all_properties) ?> properties
            </p>

            <div id="properties-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">

                <?php
                $order_index = 0;
                foreach ($all_properties as $property):
                    $type       = $property['type'] ?? 'apartment';
                    $slug_raw   = $property['slug'];
                    $slug       = htmlspecialchars($slug_raw, ENT_QUOTES, 'UTF-8');
                    $title      = htmlspecialchars($property['title'], ENT_QUOTES, 'UTF-8');
                    $location   = htmlspecialchars($property['location'] ?? '', ENT_QUOTES, 'UTF-8');
                    $neighborhood = htmlspecialchars($property['neighborhood'] ?? '', ENT_QUOTES, 'UTF-8');
                    $city       = htmlspecialchars($property['city'] ?? 'Tbilisi', ENT_QUOTES, 'UTF-8');
                    $bedrooms   = (int)($property['bedrooms'] ?? 0);
                    $bathrooms  = (int)($property['bathrooms'] ?? 0);
                    $sqft       = (int)($property['sqft'] ?? 0);
                    $price      = (int)($property['price'] ?? 0);
                    $status     = htmlspecialchars($property['status'] ?? 'For Sale', ENT_QUOTES, 'UTF-8');
                    $country    = htmlspecialchars($property['country'] ?? 'Georgia', ENT_QUOTES, 'UTF-8');
                    $offer      = (stripos($property['status'] ?? 'For Sale', 'rent') !== false) ? 'rent' : 'sale';
                    $short_desc = htmlspecialchars(mb_strimwidth($property['short_description'] ?? $property['meta_description'] ?? '', 0, 115, '…'), ENT_QUOTES, 'UTF-8');
                    $card_image = htmlspecialchars($property['card_image'] ?? '', ENT_QUOTES, 'UTF-8');

                    $type_cfg = [
                        'apartment' => ['label' => 'Apartment', 'classes' => 'bg-blue-100 text-blue-800', 'icon' => 'building-2'],
                        'house'     => ['label' => 'House',     'classes' => 'bg-emerald-100 text-emerald-800', 'icon' => 'home'],
                    ];
                    $tc = $type_cfg[$type] ?? $type_cfg['apartment'];

                    $detail_url = BASE_URL . 'properties/details/' . $slug_raw;
                    $detail_url = htmlspecialchars($detail_url, ENT_QUOTES, 'UTF-8');
                ?>
                <article
                    class="property-card bg-white rounded-2xl shadow-md overflow-hidden hover:shadow-xl transition-shadow duration-300 flex flex-col"
                    data-sal="fade"
                    data-sal-delay="<?= min($order_index * 100, 500) ?>"
                    data-type="<?= $type ?>"
                    data-purpose="<?= $offer ?>"
                    data-country="<?= $country ?>"
                    data-city="<?= $city ?>"
                    data-title="<?= strtolower($title) ?>"
                    data-location="<?= strtolower($location . ' ' . $neighborhood) ?>"
                    data-price="<?= $price ?>"
                    data-sqft="<?= $sqft ?>"
                    data-bedrooms="<?= $bedrooms ?>"
                    data-bathrooms="<?= $bathrooms ?>"
                    data-order="<?= $order_index++ ?>"
                    data-slug="<?= $slug ?>">

                    <div class="relative flex-shrink-0">
                        <img src="<?= $card_image ?>"
                             alt="<?= $title ?>"
                             class="w-full h-48 object-cover"
                             loading="lazy"
                             width="600" height="400">

                        <span class="absolute top-3 left-3 flex items-center gap-1 <?= htmlspecialchars($tc['classes'], ENT_QUOTES, 'UTF-8') ?> text-xs font-semibold px-2.5 py-1 rounded-full shadow-sm">
                            <i data-lucide="<?= htmlspecialchars($tc['icon'], ENT_QUOTES, 'UTF-8') ?>" class="w-3 h-3" aria-hidden="true"></i>
                            <?= htmlspecialchars($tc['label'], ENT_QUOTES, 'UTF-8') ?>
                        </span>

                        <span class="absolute top-3 right-3 bg-emerald-600 text-white px-3 py-1 rounded-full text-sm font-bold shadow">
                            $<?= number_format($price) ?>
                        </span>

                        <span class="absolute bottom-3 right-3 bg-black/60 text-white text-xs px-2.5 py-1 rounded-full backdrop-blur-sm">
                            <?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8') ?>
                        </span>
                    </div>

                    <div class="p-5 flex flex-col flex-1">
                        <h3 class="text-lg font-bold text-gray-800 mb-1 leading-snug"><?= $title ?></h3>
                        <p class="text-gray-500 text-sm mb-2 flex items-center gap-1">
                            <i data-lucide="map-pin" class="w-3.5 h-3.5" aria-hidden="true"></i>
                            <?= $neighborhood ? $neighborhood . ', ' : '' ?><?= $city ?>
                        </p>
                        <p class="text-gray-500 text-sm mb-4 leading-relaxed flex-1"><?= $short_desc ?></p>

                        <div class="grid grid-cols-3 gap-2 mb-5 text-sm text-gray-600">
                            <div class="flex items-center gap-1.5 bg-gray-50 rounded-lg px-3 py-2 justify-center">
                                <i data-lucide="bed" class="w-4 h-4 text-emerald-500 flex-shrink-0" aria-hidden="true"></i>
                                <span><?= $bedrooms ?> <?= $bedrooms === 1 ? 'Bed' : 'Beds' ?></span>
                            </div>
                            <div class="flex items-center gap-1.5 bg-gray-50 rounded-lg px-3 py-2 justify-center">
                                <i data-lucide="bath" class="w-4 h-4 text-emerald-500 flex-shrink-0" aria-hidden="true"></i>
                                <span><?= $bathrooms ?> <?= $bathrooms === 1 ? 'Bath' : 'Baths' ?></span>
                            </div>
                            <div class="flex items-center gap-1.5 bg-gray-50 rounded-lg px-3 py-2 justify-center">
                                <i data-lucide="square" class="w-4 h-4 text-emerald-500 flex-shrink-0" aria-hidden="true"></i>
                                <span><?= number_format($sqft) ?> ft²</span>
                            </div>
                        </div>

                        <div class="flex gap-2 mt-auto">
                            <a href="<?= $detail_url ?>"
                               class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white text-center px-4 py-2.5 rounded-lg font-semibold text-sm transition-colors duration-200">
                                View Details
                            </a>
                            <a href="https://wa.me/<?= CONTACT_PHONE ?>?text=Hi!%20I'm%20interested%20in%20<?= rawurlencode($title) ?>%20listed%20at%20$<?= $price ?>"
                               target="_blank" rel="noopener noreferrer"
                               class="flex items-center justify-center gap-1.5 bg-green-500 hover:bg-green-600 text-white px-4 py-2.5 rounded-lg font-semibold text-sm transition-colors duration-200">
                                <img src="<?= BASE_URL ?>img/Logos/si-whatsapp-w.svg" alt="" class="w-4 h-4" aria-hidden="true">
                                Enquire
                            </a>
                        </div>
                    </div>

                </article>
                <?php endforeach; ?>

            </div><!-- /properties-grid -->

            <div id="no-results" class="hidden text-center py-20">
                <i data-lucide="search-x" class="w-12 h-12 text-gray-300 mx-auto mb-4" aria-hidden="true"></i>
                <h3 class="text-xl font-semibold text-gray-500 mb-2">No properties found</h3>
                <p class="text-gray-400 mb-6">Try a different search term or clear your filters.</p>
                <button id="reset-filters" class="text-emerald-600 font-semibold hover:underline">Clear all filters</button>
            </div>

        </div>
    </div>

    <!-- Why section -->
    <section class="py-12 bg-white" aria-labelledby="why-heading">
        <div class="container mx-auto px-4">
            <div class="text-center mb-10">
                <h2 id="why-heading" class="text-3xl md:text-4xl font-bold text-gray-800 mb-3">Why QMAX Realty?</h2>
                <p class="text-gray-600 max-w-xl mx-auto">Expert guidance for buying, selling, or renting properties in Georgia.</p>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8 max-w-5xl mx-auto">
                <?php
                $why = [
                    ['icon' => 'star',          'title' => '5★ Rated',      'desc' => 'Hundreds of 5-star reviews from satisfied clients.'],
                    ['icon' => 'shield-check',   'title' => 'Trusted &amp; Transparent', 'desc' => 'Clear communication and ethical practices throughout.'],
                    ['icon' => 'map-pin',      'title' => 'Local Expertise', 'desc' => 'Deep knowledge of Tbilisi neighborhoods and emerging markets.'],
                    ['icon' => 'message-circle', 'title' => '24/7 Support',   'desc' => 'We\'re available 7 days a week via WhatsApp.'],
                ];
                foreach ($why as $w): ?>
                <div class="text-center">
                    <div class="w-14 h-14 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-3">
                        <i data-lucide="<?= $w['icon'] ?>" class="w-7 h-7 text-emerald-600" aria-hidden="true"></i>
                    </div>
                    <h3 class="font-semibold text-gray-800 mb-1"><?= $w['title'] ?></h3>
                    <p class="text-sm text-gray-600"><?= $w['desc'] ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="container mx-auto px-4 py-8 mb-8">
        <div class="bg-gray-50 rounded-2xl p-8 text-center">
            <h2 class="text-2xl font-bold text-gray-800 mb-2">Looking for Something Specific?</h2>
            <p class="text-gray-600 mb-6 max-w-md mx-auto">
                Tell us what you're looking for and we'll find the perfect property for you.
            </p>
            <a href="https://wa.me/<?= CONTACT_PHONE ?>?text=Hi!%20I'm%20looking%20for%20a%20property%20in%20Georgia."
               target="_blank" rel="noopener noreferrer"
               class="inline-flex items-center bg-green-500 hover:bg-green-600 text-white px-8 py-3 rounded-xl font-semibold transition-colors duration-200">
                <img src="<?= BASE_URL ?>img/Logos/si-whatsapp-w.svg" alt="" class="w-5 h-5 mr-2" aria-hidden="true">
                Ask Us on WhatsApp
            </a>
        </div>
    </section>

</main>

<?php require_once __DIR__ . '/includes/navbar.php'; ?>
<?php require_once __DIR__ . '/includes/scroll-top.php'; ?>
<?php require_once __DIR__ . '/includes/footer.php'; ?>

<?php qmx_foot(); ?>