<?php
declare(strict_types=1);

// Route: /properties/details/<slug>  (rewritten by .htaccess -> ?slug=)
// Builds the page context and hands off to the shared property detail template.

require_once __DIR__ . '/../includes/layout.php'; // defines BASE_URL, SITE_URL, qmx_head(), qmx_foot(), CSRF

$all_properties = require __DIR__ . '/../config/properties.php';

$slug = $_GET['slug'] ?? '';

if ($slug === '' || !isset($all_properties[$slug])) {
    http_response_code(404);
    $property      = null;
    $page_title    = 'Property Not Found — QMAX Realty';
    $page_desc     = 'The property you are looking for could not be found. Browse our premium real estate listings instead.';
} else {
    $property      = $all_properties[$slug];
    $page_title    = ($property['title'] ?? 'Property') . ' — QMAX Realty';
    $page_desc     = $property['meta_description'] ?? ($property['short_description'] ?? '');
}

include __DIR__ . '/../includes/property-detail-template.php';