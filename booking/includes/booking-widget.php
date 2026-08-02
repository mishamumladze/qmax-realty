<?php
declare(strict_types=1);

require_once __DIR__ . '/exchange.php';

// Apply admin price override if set
$_prices_override = file_exists(__DIR__ . '/../../config/prices.php')
    ? (require __DIR__ . '/../../config/prices.php')
    : [];
$_slug            = $tour['slug'] ?? '';
$widget_price     = isset($_prices_override[$_slug]) && $_prices_override[$_slug] > 0
    ? (int)$_prices_override[$_slug]
    : (int)($tour['price'] ?? 0);$widget_currency  = $tour['currency'] ?? 'GEL';
$widget_slug      = htmlspecialchars($tour['slug'] ?? '', ENT_QUOTES, 'UTF-8');
$widget_title     = htmlspecialchars($tour['title'] ?? '', ENT_QUOTES, 'UTF-8');
$widget_start     = htmlspecialchars($tour['start_time'] ?? '', ENT_QUOTES, 'UTF-8');
$widget_meeting   = htmlspecialchars($tour['meeting_point'] ?? '', ENT_QUOTES, 'UTF-8');
$min_date         = date('Y-m-d', strtotime('+1 day'));
$max_date         = date('Y-m-d', strtotime('+365 days'));
?>

<!-- Price header -->
<div class="bg-emerald-600 px-6 py-5 text-white text-center">
    <p class="text-sm font-medium opacity-70 mb-1">Price per person</p>
    <p class="text-4xl font-extrabold leading-none">
        <?= $widget_price ?><span class="text-lg font-medium ml-1"><?= $widget_currency ?></span>
    </p>
    <div class="flex items-center justify-center gap-1 mt-2">
        <span class="text-emerald-300 text-lg" aria-label="5 star rating">★★★★★</span>
        <span class="text-white/70 text-sm">5.0 · Verified reviews</span>
    </div>
</div>

<div class="px-6 py-5">

    <!-- Key info (always visible) -->
    <div class="space-y-3 text-sm mb-5">
        <div class="flex items-center">
            <i data-lucide="calendar" class="h-5 w-5 text-emerald-600 mr-3 flex-shrink-0" aria-hidden="true"></i>
            <div>
                <p class="font-semibold text-gray-800">Departure</p>
                <p class="text-gray-600"><?= $widget_start ?></p>
            </div>
        </div>
        <div class="flex items-start">
            <i data-lucide="map-pin" class="h-5 w-5 text-emerald-600 mr-3 mt-0.5 flex-shrink-0" aria-hidden="true"></i>
            <div>
                <p class="font-semibold text-gray-800">Pickup point</p>
                <p class="text-gray-600"><?= $widget_meeting ?></p>
            </div>
        </div>
    </div>

    <!-- Book Now button (hidden once form is open) -->
    <button id="qmx-book-now-btn" type="button"
            class="w-full bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-bold text-lg py-4 px-6 rounded-xl shadow-md transition-all duration-200 flex items-center justify-center gap-2">
        <i data-lucide="calendar-check" class="w-5 h-5" aria-hidden="true"></i>
        Book Now
    </button>

    <!-- Collapsible form area -->
    <div id="qmx-form-collapse" class="overflow-hidden max-h-0 transition-all duration-500 ease-in-out">
        <div class="pt-4">

            <!-- Step 1: booking form -->
            <div id="qmx-booking-form-wrap">
                <form id="qmx-booking-form" novalidate>
                    <input type="hidden" name="property_slug" value="<?= $widget_slug ?>">

                    <div class="grid grid-cols-2 gap-3 mb-3">
                        <div>
                            <label for="qmx-first-name" class="block text-xs font-semibold text-gray-700 mb-1">First name *</label>
                            <input id="qmx-first-name" name="first_name" type="text" required autocomplete="given-name"
                                   placeholder="John"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        </div>
                        <div>
                            <label for="qmx-last-name" class="block text-xs font-semibold text-gray-700 mb-1">Last name *</label>
                            <input id="qmx-last-name" name="last_name" type="text" required autocomplete="family-name"
                                   placeholder="Smith"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="qmx-email" class="block text-xs font-semibold text-gray-700 mb-1">Email *</label>
                        <input id="qmx-email" name="email" type="email" required autocomplete="email"
                               placeholder="john@example.com"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>

                    <div class="mb-3">
                        <label for="qmx-phone" class="block text-xs font-semibold text-gray-700 mb-1">Phone *</label>
                        <input id="qmx-phone" name="phone" type="tel" required autocomplete="tel"
                               placeholder="+1 555 000 0000"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>

                    <div class="mb-3">
                        <label for="qmx-language" class="block text-xs font-semibold text-gray-700 mb-1">Guide language *</label>
                        <select id="qmx-language" name="language" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            <option value="">Select language…</option>
                            <option value="English">🇬🇧 English</option>
                            <option value="Russian">🇷🇺 Russian</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-3 mb-3">
                        <div>
                            <label for="qmx-date" class="block text-xs font-semibold text-gray-700 mb-1">Tour date *</label>
                            <input id="qmx-date" name="property_date" type="date" required
                                   min="<?= $min_date ?>" max="<?= $max_date ?>"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        </div>
                        <div>
                            <label for="qmx-pax" class="block text-xs font-semibold text-gray-700 mb-1">Passengers *</label>
                            <select id="qmx-pax" name="pax" required
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                <?php for ($i = 1; $i <= 15; $i++): ?>
                                <option value="<?= $i ?>"><?= $i ?> <?= $i === 1 ? 'person' : 'people' ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="qmx-notes" class="block text-xs font-semibold text-gray-700 mb-1">Notes (optional)</label>
                        <textarea id="qmx-notes" name="notes" rows="2" placeholder="Allergies, special requests…"
                                  class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 resize-none"></textarea>
                    </div>

                    <!-- Live price summary -->
                    <div id="qmx-price-summary" class="bg-emerald-50 border border-emerald-200 rounded-lg px-4 py-3 mb-4 text-sm hidden">
                        <div class="flex justify-between font-semibold text-gray-800">
                            <span id="qmx-summary-label">Total</span>
                            <span id="qmx-summary-price"></span>
                        </div>
                        <p class="text-xs text-gray-500 mt-0.5">Charged in USD via PayPal</p>
                    </div>

                    <!-- Error message -->
                    <div id="qmx-form-error" class="bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg px-4 py-3 mb-4 hidden" role="alert"></div>

                    <!-- Submit -->
                    <button type="submit" id="qmx-submit-btn"
                            class="w-full bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-bold text-base py-3 px-6 rounded-xl shadow-md transition-all duration-200 flex items-center justify-center gap-2">
                        <i data-lucide="credit-card" class="w-5 h-5" aria-hidden="true"></i>
                        <span id="qmx-submit-label">Continue to Payment</span>
                    </button>
                </form>
            </div>

            <!-- Step 2: PayPal -->
            <div id="qmx-paypal-wrap" class="hidden">
                <div class="bg-emerald-50 border border-emerald-200 rounded-lg px-4 py-3 mb-4 text-sm">
                    <p class="font-semibold text-emerald-800 mb-0.5" id="qmx-paypal-summary"></p>
                    <p class="text-emerald-700">Complete your payment below to confirm.</p>
                </div>
                <div id="qmx-paypal-buttons"></div>
                <button id="qmx-back-btn" type="button"
                        class="mt-3 w-full text-sm text-gray-500 hover:text-gray-700 underline underline-offset-2">
                    ← Back to booking form
                </button>
            </div>

            <!-- Step 3: Success -->
            <div id="qmx-success-wrap" class="hidden text-center py-4">
                <i data-lucide="check-circle-2" class="w-12 h-12 text-green-500 mx-auto mb-3" aria-hidden="true"></i>
                <h3 class="text-lg font-bold text-gray-800 mb-1">Booking Confirmed!</h3>
                <p id="qmx-success-ref" class="text-sm text-gray-600 mb-2"></p>
                <p class="text-xs text-gray-500">Check your email for your ticket.</p>
            </div>

        </div><!-- /pt-4 -->
    </div><!-- /qmx-form-collapse -->

    <!-- Partner trust logos -->
    <div class="mt-5 pt-4 border-t border-gray-200">
        <p class="text-xs text-gray-500 text-center mb-3">Trusted by travellers on</p>
        <div class="flex justify-center items-center gap-4 flex-wrap">
            <a href="https://www.getyourguide.com/funtravelgeorgia-s516983" target="_blank" rel="noopener noreferrer">
                <img src="<?= BASE_URL ?>img/Partners/GYG.svg" alt="GetYourGuide" class="h-7" loading="lazy">
            </a>
            <!-- <a href="https://www.viator.com/operator/222230" target="_blank" rel="noopener noreferrer">
                <img src="<?= BASE_URL ?>img/Partners/Viator.svg" alt="Viator" class="h-5" loading="lazy">
            </a> -->
            <a href="https://www.tripadvisor.com/Attraction_Review-g294195-d25772171" target="_blank" rel="noopener noreferrer">
                <img src="<?= BASE_URL ?>img/Partners/TripAdvisor.webp" alt="TripAdvisor" class="h-8" loading="lazy">
            </a>
        </div>
    </div>

</div>

<script>
(function () {
    'use strict';

    const INIT_URL    = <?= json_encode(BASE_URL . 'booking/api/inquiry-init.php',    JSON_UNESCAPED_SLASHES) ?>;
    const CONFIRM_URL = <?= json_encode(BASE_URL . 'booking/api/booking-confirm.php', JSON_UNESCAPED_SLASHES) ?>;
    const property_slug   = <?= json_encode($tour['slug'] ?? '', JSON_UNESCAPED_UNICODE) ?>;
    const PRICE_GEL   = <?= $widget_price ?>;
    const GEL_TO_USD  = <?= qmx_gel_to_usd() ?>;
    const CSRF_TOKEN  = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    const bookNowBtn  = document.getElementById('qmx-book-now-btn');
    const collapse    = document.getElementById('qmx-form-collapse');
    const formWrap    = document.getElementById('qmx-booking-form-wrap');
    const form        = document.getElementById('qmx-booking-form');
    const submitBtn   = document.getElementById('qmx-submit-btn');
    const submitLabel = document.getElementById('qmx-submit-label');
    const errorBox    = document.getElementById('qmx-form-error');
    const priceSummary  = document.getElementById('qmx-price-summary');
    const summaryLabel  = document.getElementById('qmx-summary-label');
    const summaryPrice  = document.getElementById('qmx-summary-price');
    const paxSelect     = document.getElementById('qmx-pax');
    const paypalWrap    = document.getElementById('qmx-paypal-wrap');
    const paypalSummary = document.getElementById('qmx-paypal-summary');
    const paypalButtons = document.getElementById('qmx-paypal-buttons');
    const backBtn       = document.getElementById('qmx-back-btn');
    const successWrap   = document.getElementById('qmx-success-wrap');
    const successRef    = document.getElementById('qmx-success-ref');

    // ── Book Now toggle ───────────────────────────────────────────────────────
    bookNowBtn.addEventListener('click', function () {
        collapse.style.maxHeight = collapse.scrollHeight + 'px';
        bookNowBtn.style.transition = 'opacity 0.2s';
        bookNowBtn.style.opacity    = '0';
        setTimeout(() => bookNowBtn.style.display = 'none', 200);
        collapse.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    });

    // ── Live price summary ────────────────────────────────────────────────────
    function updatePriceSummary() {
        const pax      = parseInt(paxSelect.value, 10) || 1;
        const totalGel = PRICE_GEL * pax;
        const totalUsd = (totalGel * GEL_TO_USD).toFixed(2);
        summaryLabel.textContent = pax + ' × ' + PRICE_GEL + '₾';
        summaryPrice.textContent = totalGel + '₾ (~$' + totalUsd + ')';
        priceSummary.classList.remove('hidden');
        collapse.style.maxHeight = collapse.scrollHeight + 'px';
    }
    paxSelect.addEventListener('change', updatePriceSummary);

    // ── Helpers ───────────────────────────────────────────────────────────────
    function showError(msg) {
        errorBox.textContent = msg;
        errorBox.classList.remove('hidden');
        collapse.style.maxHeight = collapse.scrollHeight + 'px';
    }
    function clearError() {
        errorBox.textContent = '';
        errorBox.classList.add('hidden');
    }
    function setLoading(loading) {
        submitBtn.disabled       = loading;
        submitLabel.textContent  = loading ? 'Processing…' : 'Continue to Payment';
    }

    let currentReference = null;
    let currentPriceUsd  = null;

    // ── Step 1: submit form ───────────────────────────────────────────────────
    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        clearError();
        setLoading(true);

        const data = Object.fromEntries(new FormData(form).entries());

        try {
            const res  = await fetch(INIT_URL, {
                method:  'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF_TOKEN },
                body:    JSON.stringify(data),
            });
            const json = await res.json();

            if (!json.ok) {
                showError(json.error || 'Something went wrong. Please try again.');
                setLoading(false);
                return;
            }

            currentReference = json.reference;
            currentPriceUsd  = json.price_usd;

            formWrap.classList.add('hidden');
            paypalSummary.textContent = 'Total: $' + currentPriceUsd + ' USD for ' + data.pax + ' person(s)';
            paypalWrap.classList.remove('hidden');
            collapse.style.maxHeight = collapse.scrollHeight + 'px';
            renderPayPal();

        } catch (err) {
            showError('Network error. Please check your connection and try again.');
            setLoading(false);
        }
    });

    // ── Back button ───────────────────────────────────────────────────────────
    backBtn.addEventListener('click', function () {
        paypalWrap.classList.add('hidden');
        paypalButtons.innerHTML = '';
        formWrap.classList.remove('hidden');
        collapse.style.maxHeight = collapse.scrollHeight + 'px';
        setLoading(false);
        currentReference = null;
    });

    // ── Step 2: PayPal ────────────────────────────────────────────────────────
    function renderPayPal() {
        paypalButtons.innerHTML = '';

        if (typeof paypal === 'undefined') {
            paypalButtons.innerHTML = '<p class="text-red-600 text-sm text-center">PayPal failed to load. Please refresh and try again.</p>';
            return;
        }

        paypal.Buttons({
            style: { layout: 'vertical', color: 'gold', shape: 'rect', label: 'pay' },

            createOrder: function (_data, actions) {
                return actions.order.create({
                    purchase_units: [{ amount: { value: currentPriceUsd, currency_code: 'USD' },
                                       description: <?= json_encode($tour['title'] ?? '') ?> }],
                });
            },

            onApprove: async function (data) {
                paypalButtons.innerHTML = '<p class="text-center text-sm text-gray-600 py-4">Confirming your booking…</p>';
                try {
                    const res  = await fetch(CONFIRM_URL, {
                        method:  'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF_TOKEN },
                        body:    JSON.stringify({ reference: currentReference, paypal_order_id: data.orderID }),
                    });
                    const json = await res.json();
                    if (json.ok) {
                        paypalWrap.classList.add('hidden');
                        successRef.textContent = 'Reference: ' + currentReference;
                        successWrap.classList.remove('hidden');
                        collapse.style.maxHeight = collapse.scrollHeight + 'px';
                    } else {
                        paypalButtons.innerHTML = '<p class="text-red-600 text-sm text-center">Confirmation failed: ' + (json.error || 'unknown error') + '</p>';
                    }
                } catch (err) {
                    paypalButtons.innerHTML = '<p class="text-red-600 text-sm text-center">Network error. Contact us with reference: ' + currentReference + '</p>';
                }
            },

            onError:  function (err) {
                console.error('PayPal error:', err);
                paypalButtons.innerHTML = '<p class="text-red-600 text-sm text-center">Payment could not be completed. Please try again.</p>';
            },
            onCancel: function () { backBtn.click(); },

        }).render('#qmx-paypal-buttons');
    }

})();
</script>