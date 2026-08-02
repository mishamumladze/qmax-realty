# QMAX Realty

QMAX Realty is a real estate website for properties in Georgia (Tbilisi). It is built with vanilla PHP, Tailwind CSS v4, and a lightweight set of JavaScript enhancements.

## Features

- **Property listings** with advanced client-side filtering — availability (sale/rent), property type, country, city, bedrooms, bathrooms and price presets, plus an active-filter chip bar and a dedicated filter modal.
- **Property detail pages** — full spec sheets (`/properties/details/<slug>`), galleries, floor plans, location/nearby info, reviews and an enquiry contact card (WhatsApp / phone / contact form).
- **Swup-powered page transitions** (bundled core) and scroll-reveal animations (SAL).
- **Navigation** — responsive top/bottom navbar with a slide-up mobile menu and GTranslate EN/RU language widget.
- **Contact & newsletter** forms, socials page, and an XML sitemap.
- **Admin + booking backend** (reserved for future expansion; currently present but not part of the public surface).

## Requirements

- PHP 8.x
- Apache (`.htaccess` provided) — the site is built to run under XAMPP/cPanel-style hosting.
- Node/npm only if you need to rebuild the compiled CSS.

## Installation

1. Clone the repo into your web root:
   ```bash
   git clone https://github.com/mishamumladze/qmax-realty.git
   ```
2. Serve it via a PHP web server or XAMPP pointing at the cloned folder.
3. All content lives in `config/properties.php` (the single source of truth for property data). Edit it to add, remove, or change listings.

## File structure

- `index.php` — home page
- `listings.php` — property search + listings with filters
- `properties/details.php` — property detail router (reads `?slug=`)
- `includes/` — shared layout, navbar, footer, detail template
- `config/properties.php` — property data source
- `config/app.php` — site-wide constants (URL, contact info, analytics)
- `admin/`, `booking/` — reserved
- `js/main.js` — all page behavior + filter engine

## Build (CSS)

Tailwind v4 is used. To rebuild styles:

```bash
npm install
npm run build
```

`npm run css:watch` rebuilds automatically during development.

## License

Private project — all rights reserved.