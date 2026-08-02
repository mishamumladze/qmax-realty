/**
 * QMAX Realty — Main JS
 *
 * Handles: mobile menu, homepage hero carousel, index accordion tabs,
 * learn-more arrow animation, and scroll-to-top.
 *
 * NOTE: lucide.createIcons() is NOT called here.
 * It is called once by qmx_foot() in includes/layout.php, which runs on
 * every page after all scripts have loaded.
 */

'use strict';

// -------------------------------------------------------------------------
// Mobile Menu Toggle
// -------------------------------------------------------------------------
function initializeMobileMenu() {
    const button  = document.getElementById('mobile-menu-toggle');
    const menu    = document.getElementById('slide-menu');
    const overlay = document.getElementById('slide-overlay');
    const bar     = document.getElementById('slide-menu-bar');

    if (!button || !menu) return;

    let isOpen = false;

    // Query fresh each call — Lucide replaces <i> with <svg> after init,
    // so cached references would point to detached nodes.
    function getIcons() {
        return {
            hamburger: document.getElementById('hamburger-icon'),
            close:     document.getElementById('close-icon'),
        };
    }

    function openMenu() {
        isOpen = true;
        button.setAttribute('aria-expanded', 'true');
        menu.classList.remove('translate-y-full');
        menu.classList.add('translate-y-0');
        if (overlay) {
            overlay.classList.remove('hidden', 'opacity-0');
            overlay.classList.add('opacity-100');
        }
        document.body.style.overflow = 'hidden';
        const { hamburger, close } = getIcons();
        if (hamburger) hamburger.classList.add('hidden');
        if (close)     close.classList.remove('hidden');
    }

    function closeMenu() {
        isOpen = false;
        button.setAttribute('aria-expanded', 'false');
        menu.classList.add('translate-y-full');
        menu.classList.remove('translate-y-0');
        if (overlay) {
            overlay.classList.add('opacity-0');
            overlay.classList.remove('opacity-100');
            setTimeout(() => overlay.classList.add('hidden'), 300);
        }
        document.body.style.overflow = '';
        const { hamburger, close } = getIcons();
        if (hamburger) hamburger.classList.remove('hidden');
        if (close)     close.classList.add('hidden');
    }

    button.addEventListener('click', (e) => {
        e.stopPropagation();
        isOpen ? closeMenu() : openMenu();
    });

    menu.querySelectorAll('a').forEach(link => {
        link.addEventListener('click', closeMenu);
    });

    if (overlay) overlay.addEventListener('click', closeMenu);

    if (bar) bar.addEventListener('click', closeMenu);

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && isOpen) closeMenu();
    });
}

// -------------------------------------------------------------------------
// Homepage Hero Carousel
// No-op guard: the homepage hero is currently a static image.
// If a multi-slide carousel is added later, implement the logic here.
// -------------------------------------------------------------------------
function initHeroCarousel() {
    const slides = document.querySelectorAll('.hero-slide');
    if (slides.length < 2) return; // nothing to cycle

    let current = 0;
    setInterval(() => {
        slides[current].classList.add('hidden');
        current = (current + 1) % slides.length;
        slides[current].classList.remove('hidden');
    }, 4000);
}

// -------------------------------------------------------------------------
// Homepage Accordion Tabs (Group / Private / Transfers)
// -------------------------------------------------------------------------
function initAccordionTabs() {
    const tabButtons = document.querySelectorAll('.tab-btn');
    if (tabButtons.length === 0) return;

    const tabContents = document.querySelectorAll('.tab-content');
    let openTab = null;

    function closeAll() {
        tabContents.forEach(c => {
            c.style.maxHeight    = '0';
            c.style.opacity      = '0';
            c.style.pointerEvents = 'none';
        });
        tabButtons.forEach(b => {
            b.classList.remove('active', 'bg-emerald-600', 'text-white');
            b.setAttribute('aria-expanded', 'false');
            const chevron = b.querySelector('.accordion-chevron');
            if (chevron) chevron.style.transform = 'rotate(0deg)';
        });
        openTab = null;
    }

    function openPanel(tabKey, btn) {
        const content = document.getElementById(tabKey + '-tab');
        if (!content) return;
        content.style.opacity       = '1';
        content.style.pointerEvents = 'auto';
        // Deferred to allow transition
        requestAnimationFrame(() => {
            content.style.maxHeight = content.scrollHeight + 'px';
        });
        btn.classList.add('active', 'bg-emerald-600', 'text-white');
        btn.setAttribute('aria-expanded', 'true');
        const chevron = btn.querySelector('.accordion-chevron');
        if (chevron) chevron.style.transform = 'rotate(180deg)';
        openTab = tabKey;
    }

    tabButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            const tab = btn.getAttribute('data-tab');
            if (openTab === tab) {
                closeAll();
            } else {
                closeAll();
                openPanel(tab, btn);
            }
        });
    });

    // Recompute open tab height on window resize
    window.addEventListener('resize', () => {
        if (!openTab) return;
        const content = document.getElementById(openTab + '-tab');
        const btn     = document.querySelector('.tab-btn.active');
        if (content && btn) openPanel(openTab, btn);
    });

    closeAll();
}

// -------------------------------------------------------------------------
// "Learn More" Arrow Animation (homepage cards)
// -------------------------------------------------------------------------
function initLearnMoreAnimations() {
    document.querySelectorAll('.learn-more-btn').forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            const href  = this.getAttribute('href');
            const arrow = this.querySelector('.learn-more-arrow');
            const card  = this.closest('.bg-white, article');

            if (arrow && card) {
                const cardRect  = card.getBoundingClientRect();
                const arrowRect = arrow.getBoundingClientRect();
                const distance  = cardRect.right - arrowRect.left - 20;

                // Draw line trail
                const trail = document.createElement('div');
                trail.style.cssText = `
                    position:absolute; height:2px;
                    background:linear-gradient(90deg,#047857,#34d399);
                    top:${arrowRect.top - cardRect.top + arrowRect.height / 2}px;
                    left:${arrowRect.left - cardRect.left}px;
                    width:0; transform:translateY(-50%); z-index:10;
                    transition:width 0.2s ease;
                `;
                card.style.position = 'relative';
                card.appendChild(trail);

                arrow.style.transform = `translateX(${distance}px) scale(1.2)`;
                arrow.style.color     = '#047857';
                setTimeout(() => { trail.style.width = distance + 'px'; }, 50);
            }

            setTimeout(() => {
                if (href) window.location.href = href;
            }, 400);
        });
    });
}

// -------------------------------------------------------------------------
// Scroll to Top Button (shared across all pages)
// -------------------------------------------------------------------------
function initScrollToTop() {
    const btn = document.getElementById('scrollToTopBtn');
    if (!btn) return;

    window.addEventListener('scroll', () => {
        btn.classList.toggle('hidden', window.scrollY <= 200);
    }, { passive: true });

    btn.addEventListener('click', () => {
        const start    = window.scrollY;
        const duration = 900;
        let startTime  = null;
        const ease = t => t < 0.5 ? 2 * t * t : -1 + (4 - 2 * t) * t;

        function step(ts) {
            if (!startTime) startTime = ts;
            const progress = Math.min((ts - startTime) / duration, 1);
            window.scrollTo(0, start * (1 - ease(progress)));
            if (progress < 1) requestAnimationFrame(step);
        }
        requestAnimationFrame(step);
    });
}

(function () {
    const grid      = document.getElementById('properties-grid');
    if (!grid) return;
    const cards     = Array.from(grid.querySelectorAll('.property-card'));
    const searchEl  = document.getElementById('property-search');
    const sortEl    = document.getElementById('sort-select');
    const tabs      = Array.from(document.querySelectorAll('.filter-tab'));
    const noResults = document.getElementById('no-results');
    const countEl   = document.getElementById('visible-count');
    const resetBtn  = document.getElementById('reset-filters');

    // Advanced-filter modal + sticky-bar FX
    const fltBtn     = document.getElementById('flt-open-btn');
    const badgeEl    = document.getElementById('flt-count-badge');
    const modal      = document.getElementById('flt-modal');
    const backdrop   = document.getElementById('flt-backdrop');
    const closeBtn   = document.getElementById('flt-close-btn');
    const applyBtn   = document.getElementById('flt-apply-btn');
    const clearBtn   = document.getElementById('flt-clear-btn');
    const offerWrap  = document.getElementById('flt-offer');
    const offerPills = Array.from(offerWrap ? offerWrap.querySelectorAll('[data-offer-pill]') : []);
    const typeWrap   = document.getElementById('flt-type');
    const typePills  = Array.from(typeWrap ? typeWrap.querySelectorAll('[data-type-pill]') : []);
    const bedEl      = document.getElementById('flt-bedrooms');
    const bathEl     = document.getElementById('flt-bathrooms');
    const countryEl  = document.getElementById('flt-country');
    const cityEl     = document.getElementById('flt-city');
    const priceMinEl = document.getElementById('flt-price-min');
    const priceMaxEl = document.getElementById('flt-price-max');
    const presetWrap = document.getElementById('flt-presets');
    const presetBtns = Array.from(presetWrap ? presetWrap.querySelectorAll('[data-preset]') : []);
    const chipsRow   = document.getElementById('flt-active-chips');
    const chipsList  = document.getElementById('flt-chips-list');
    const clearChips = document.getElementById('flt-clear-chips');

    const validFilters = ['all', 'apartment', 'house'];
    const urlParam     = new URLSearchParams(window.location.search).get('filter') || '';
    let activeFilter   = validFilters.includes(urlParam) ? urlParam : 'all';
    let searchQuery    = '';
    let sortMode       = 'default';

    // ---- Advanced widget filter state ----
    const filters = {
        offer:     'all',  // 'all' | 'sale' | 'rent'
        bedrooms:  0,      // 0 = any; else minimum
        bathrooms: 0,      // 0 = any; else minimum
        country:   '',
        city:      '',
        priceMin:  null,
        priceMax:  null,
    };
    let activePreset = '';

    function setTabActive(key) {
        tabs.forEach(t => {
            const on = t.dataset.filter === key;
            t.classList.toggle('active', on);
            t.setAttribute('aria-selected', on ? 'true' : 'false');
        });
        typePills.forEach(p => p.classList.toggle('flt-pill-active', p.dataset.typePill === key));
    }

    function setTypePills(key) {
        typePills.forEach(p => p.classList.toggle('flt-pill-active', p.dataset.typePill === key));
    }

    function getPrice(c)     { return parseInt(c.dataset.price, 10) || 0; }
    function getSqft(c)      { return parseInt(c.dataset.sqft, 10) || 0; }
    function getOrder(c)     { return parseInt(c.dataset.order, 10) || 0; }

    // --- Widget helpers ---------------------------------------------------
    function setOfferFilter(key) {
        offerPills.forEach(p => p.classList.toggle('flt-pill-active', p.dataset.offerPill === key));
    }
    function setPreset(key) {
        presetBtns.forEach(b => b.classList.toggle('flt-preset-active', b.dataset.preset === key));
    }
    function presetRange(key) {
        switch (key) {
            case 'u200':    return { min: null,  max: 200000 };
            case '200-400': return { min: 200000, max: 400000 };
            case '400-600': return { min: 400000, max: 600000 };
            case '600p':    return { min: 600000, max: null  };
            default:        return { min: null,  max: null  };
        }
    }
    function presetKeyFor(min, max) {
        if (min === null && max === 200000)            return 'u200';
        if (min === 200000 && max === 400000)           return '200-400';
        if (min === 400000 && max === 600000)           return '400-600';
        if (min === 600000 && max === null)             return '600p';
        return '';
    }
    function fmtMoney(v) { return '$' + Number(v).toLocaleString('en-US'); }

    function activeFilterCount() {
        let n = 0;
        if (filters.offer !== 'all')            n++;
        if (filters.bedrooms > 0)               n++;
        if (filters.bathrooms > 0)              n++;
        if (filters.country)                    n++;
        if (filters.city)                       n++;
        if (filters.priceMin !== null || filters.priceMax !== null) n++;
        return n;
    }

    function updateBadge() {
        if (!badgeEl) return;
        const n = activeFilterCount();
        badgeEl.textContent = n;
        badgeEl.hidden = n === 0;
    }

    function priceLabel() {
        const min = filters.priceMin, max = filters.priceMax;
        if (min !== null && max !== null)  return fmtMoney(min) + ' – ' + fmtMoney(max);
        if (min !== null)                   return 'From ' + fmtMoney(min);
        if (max !== null)                   return 'Up to ' + fmtMoney(max);
        return '';
    }

    function renderChips() {
        if (!chipsList || !chipsRow) return;
        const chips = [];
        if (filters.offer !== 'all') chips.push({ key: 'offer',    label: filters.offer === 'sale' ? 'For Sale' : 'For Rent' });
        if (filters.bedrooms > 0)    chips.push({ key: 'bedrooms',  label: filters.bedrooms + '+ bed' });
        if (filters.bathrooms > 0)   chips.push({ key: 'bathrooms', label: filters.bathrooms + '+ bath' });
        if (filters.country)         chips.push({ key: 'country',   label: 'Country: ' + filters.country });
        if (filters.city)            chips.push({ key: 'city',      label: 'City: ' + filters.city });
        const pl = priceLabel();
        if (pl)                      chips.push({ key: 'price',     label: pl });

        chipsList.innerHTML = chips.map(c =>
            '<span class="flt-chip" data-chip-key="' + c.key + '">' + c.label +
            '<button type="button" class="flt-chip-remove" aria-label="Remove ' + c.label + '">&times;</button></span>'
        ).join('');
        chipsRow.classList.toggle('hidden', chips.length === 0);
    }

    function clearDimension(key) {
        switch (key) {
            case 'offer':    filters.offer = 'all'; setOfferFilter('all'); break;
            case 'bedrooms': filters.bedrooms = 0;  if (bedEl)      bedEl.value      = '0'; break;
            case 'bathrooms':filters.bathrooms = 0; if (bathEl)     bathEl.value     = '0'; break;
            case 'country':  filters.country = '';  if (countryEl)  countryEl.value  = '';  break;
            case 'city':     filters.city = '';     if (cityEl)     cityEl.value     = '';  break;
            case 'price':
                filters.priceMin = filters.priceMax = null;
                activePreset = '';
                setPreset('');
                if (priceMinEl) priceMinEl.value = '';
                if (priceMaxEl) priceMaxEl.value = '';
                break;
        }
        refresh();
    }

    function resetAllFilters() {
        filters.offer = 'all'; filters.bedrooms = 0; filters.bathrooms = 0;
        filters.country = ''; filters.city = '';
        filters.priceMin = filters.priceMax = null;
        activePreset = '';
        setOfferFilter('all'); setPreset('');
        if (bedEl)      bedEl.value       = '0';
        if (bathEl)     bathEl.value      = '0';
        if (countryEl)  countryEl.value   = '';
        if (cityEl)     cityEl.value      = '';
        if (priceMinEl) priceMinEl.value  = '';
        if (priceMaxEl) priceMaxEl.value  = '';
        refresh();
    }

    function applyAll() {
        const q = searchQuery.toLowerCase().trim();
        cards.forEach(card => {
            const typeOk    = activeFilter === 'all' || card.dataset.type === activeFilter;
            const textOk    = !q || card.dataset.title.includes(q) || card.dataset.location.includes(q);
            const offerOk   = filters.offer === 'all' || (card.dataset.purpose || 'sale') === filters.offer;
            const bedOk     = filters.bedrooms <= 0 || (parseInt(card.dataset.bedrooms, 10) || 0) >= filters.bedrooms;
            const bathOk    = filters.bathrooms <= 0 || (parseInt(card.dataset.bathrooms, 10) || 0) >= filters.bathrooms;
            const countryOk = !filters.country || (card.dataset.country || '') === filters.country;
            const cityOk    = !filters.city || (card.dataset.city || '') === filters.city;
            const raw       = getPrice(card);
            const priceOk   = (filters.priceMin === null || raw >= filters.priceMin) &&
                              (filters.priceMax === null || raw <= filters.priceMax);

            card.classList.toggle('hidden', !(typeOk && textOk && offerOk && bedOk && bathOk && countryOk && cityOk && priceOk));
        });

        const visible = cards.filter(c => !c.classList.contains('hidden'));
        visible.sort((a, b) => {
            switch (sortMode) {
                case 'price-asc':  return getPrice(a) - getPrice(b);
                case 'price-desc': return getPrice(b) - getPrice(a);
                case 'sqft-asc':   return getSqft(a)  - getSqft(b);
                case 'sqft-desc':  return getSqft(b)  - getSqft(a);
                default:           return getOrder(a) - getOrder(b);
            }
        });
        visible.forEach(c => grid.appendChild(c));

        const n = visible.length;
        countEl.textContent = n;
        noResults.classList.toggle('hidden', n > 0);
        grid.classList.toggle('hidden', n === 0);
    }

    function refresh() {
        applyAll();
        renderChips();
        updateBadge();
    }

    // ---- Modal -----------------------------------------------------------
    function seedControls() {
        setOfferFilter(filters.offer);
        setTypePills(activeFilter);
        if (bedEl)      bedEl.value      = filters.bedrooms;
        if (bathEl)     bathEl.value     = filters.bathrooms;
        if (countryEl)  countryEl.value  = filters.country;
        if (cityEl)     cityEl.value     = filters.city;
        if (priceMinEl) priceMinEl.value = filters.priceMin === null ? '' : filters.priceMin;
        if (priceMaxEl) priceMaxEl.value = filters.priceMax === null ? '' : filters.priceMax;
        setPreset(activePreset);
    }
    function readControls() {
        const pill = offerWrap ? offerWrap.querySelector('.flt-pill-active') : null;
        filters.offer    = pill ? pill.dataset.offerPill : 'all';
        filters.bedrooms   = bedEl      ? parseInt(bedEl.value, 10) || 0 : 0;
        filters.bathrooms  = bathEl     ? parseInt(bathEl.value, 10) || 0 : 0;
        filters.country    = countryEl  ? countryEl.value : '';
        filters.city       = cityEl     ? cityEl.value : '';
        filters.priceMin   = priceMinEl ? numInput(priceMinEl) : null;
        filters.priceMax   = priceMaxEl ? numInput(priceMaxEl) : null;
        activePreset = presetKeyFor(filters.priceMin, filters.priceMax);
        setPreset(activePreset);
        refresh();
    }
    function numInput(el) {
        const v = el.value;
        return (v === '' || isNaN(Number(v))) ? null : Number(v);
    }
    function openModal() {
        seedControls();
        modal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }
    function closeModal() {
        modal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    // ---- Event wiring ----------------------------------------------------
    offerPills.forEach(p => p.addEventListener('click', () => {
        setOfferFilter(p.dataset.offerPill);
        filters.offer = p.dataset.offerPill;
    }));
    typePills.forEach(p => p.addEventListener('click', () => {
        activeFilter = (validFilters.includes(p.dataset.typePill) ? p.dataset.typePill : 'all');
        setTabActive(activeFilter);
        applyAll();
    }));
    presetBtns.forEach(b => b.addEventListener('click', () => {
        setPreset(b.dataset.preset);
        const r = presetRange(b.dataset.preset);
        if (priceMinEl) priceMinEl.value = r.min === null ? '' : r.min;
        if (priceMaxEl) priceMaxEl.value = r.max === null ? '' : r.max;
    }));
    if (fltBtn)     fltBtn.addEventListener('click', openModal);
    if (closeBtn)   closeBtn.addEventListener('click', closeModal);
    if (backdrop)   backdrop.addEventListener('click', closeModal);
    if (applyBtn)   applyBtn.addEventListener('click', () => { readControls(); closeModal(); });
    if (clearBtn)   clearBtn.addEventListener('click', () => resetAllFilters());
    if (clearChips) clearChips.addEventListener('click', () => resetAllFilters());
    if (chipsList)  chipsList.addEventListener('click', (e) => {
        const btn = e.target.closest('.flt-chip-remove');
        if (btn) clearDimension(btn.closest('.flt-chip').dataset.chipKey);
    });
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && modal && !modal.classList.contains('hidden')) closeModal();
    });

    tabs.forEach(t => t.addEventListener('click', () => {
        activeFilter = t.dataset.filter;
        setTabActive(activeFilter);
        applyAll();
    }));
    searchEl.addEventListener('input', () => { searchQuery = searchEl.value; applyAll(); });
    sortEl.addEventListener('change', () => { sortMode = sortEl.value; applyAll(); });
    if (resetBtn) resetBtn.addEventListener('click', () => {
        activeFilter = 'all'; searchQuery = ''; sortMode = 'default';
        searchEl.value = ''; sortEl.value = 'default';
        setTabActive('all');
        resetAllFilters();
    });

    // -- Bootstrap
    setTabActive(activeFilter);

    // Support deep-links from the homepage hero: /listings?offer=sale|rent
    const offerParam = new URLSearchParams(window.location.search).get('offer') || '';
    if (offerParam === 'sale' || offerParam === 'rent') {
        filters.offer = offerParam;
        setOfferFilter(offerParam);
    }

    refresh();
})();

// -------------------------------------------------------------------------
// Boot
// -------------------------------------------------------------------------
document.addEventListener('DOMContentLoaded', () => {
    initializeMobileMenu();
    initHeroCarousel();
    initAccordionTabs();
    initLearnMoreAnimations();
    initScrollToTop();
    // lucide.createIcons() is called by qmx_foot() in includes/layout.php.
    // Do not call it here — it would fire twice on every page.
});

