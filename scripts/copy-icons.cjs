// Build-time script: generate brand SVG icons from the installed `simple-icons`
// package into img/Logos/. Two variants per brand:
//   si-<slug>.svg    — brand color (used on white backgrounds, e.g. footer)
//   si-<slug>-w.svg  — white       (used on gradient/dark backgrounds, e.g. socials cards)
//
// Run with: npm run copy:icons   (also part of `npm run build`)
//
// NOTE: this is a build tool, not a site script — it never runs in the browser.
const fs = require('fs');
const path = require('path');
const si = require('simple-icons');

const SLUGS = ['instagram', 'facebook', 'tiktok', 'telegram', 'whatsapp'];
const OUT_DIR = path.join(__dirname, '..', 'img', 'Logos');

fs.mkdirSync(OUT_DIR, { recursive: true });

let written = 0;
for (const slug of SLUGS) {
    const icon = si['si' + slug.charAt(0).toUpperCase() + slug.slice(1)];
    if (!icon) {
        console.error('copy:icons — missing simple-icons entry for "' + slug + '"');
        process.exit(1);
    }

    const src = path.join(__dirname, '..', 'node_modules', 'simple-icons', 'icons', slug + '.svg');
    const raw = fs.readFileSync(src, 'utf8');

    const variants = [
        ['si-' + slug + '.svg', icon.hex],   // brand color
        ['si-' + slug + '-w.svg', 'fff'],    // white
    ];

    for (const [file, hex] of variants) {
        const svg = raw.replace(/<svg /, '<svg fill="#' + hex + '" ');
        fs.writeFileSync(path.join(OUT_DIR, file), svg);
        written++;
    }
}

console.log('copy:icons — wrote ' + written + ' SVGs to img/Logos/');
