<?php
declare(strict_types=1);

require_once __DIR__ . '/config/app.php';
$all_properties = require __DIR__ . '/config/properties.php';

header('Content-Type: application/xml; charset=UTF-8');

$base = SITE_URL;

// Static pages
$static = [
    ['loc' => '/',         'priority' => '1.0', 'changefreq' => 'weekly'],
    ['loc' => '/listings', 'priority' => '0.9', 'changefreq' => 'weekly'],
    ['loc' => '/socials',  'priority' => '0.6', 'changefreq' => 'monthly'],
    // ['loc' => '/hotels',   'priority' => '0.6', 'changefreq' => 'monthly'],
];

// Property detail pages — built from config/properties.php
$property_urls = [];
foreach ($all_properties as $property) {
    $slug = $property['slug'];
    $property_urls[] = ['loc' => '/properties/details/' . $slug, 'priority' => '0.8', 'changefreq' => 'monthly'];
}

$all_urls = array_merge($static, $property_urls);
$today    = date('Y-m-d');

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php foreach ($all_urls as $url): ?>
    <url>
        <loc><?= htmlspecialchars($base . $url['loc'], ENT_XML1, 'UTF-8') ?></loc>
        <lastmod><?= $today ?></lastmod>
        <changefreq><?= $url['changefreq'] ?></changefreq>
        <priority><?= $url['priority'] ?></priority>
    </url>
<?php endforeach; ?>
</urlset>