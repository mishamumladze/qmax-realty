<?php

declare(strict_types=1);
require_once __DIR__ . '/../config/app.php';

/**
 * QMAX Realty — Shared Layout Functions
 *
 * Requires config/app.php to be loaded first (done in each page's require chain).
 * Uses the BASE_URL constant — never hardcodes the subfolder path.
 */

// Start session once for the whole app — provides CSRF token for all pages
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Generate CSRF token if not already set
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/**
 * Outputs the full <!DOCTYPE> … <body> opening tag.
 *
 * @param string      $title       Page title and og:title
 * @param string      $description Meta description and og:description
 * @param string      $bodyClass   Optional extra body classes
 * @param string|null $hero_image  LCP image path relative to project root (e.g. 'img/hero.webp')
 */
function qmx_head(
    string  $title       = 'QMAX Realty — Premium Real Estate in Georgia',
    string  $description = 'Discover premium real estate properties in Georgia with expert guidance and unmatched service.',
    string  $bodyClass   = 'bg-white text-gray-800',
    ?string $hero_image  = null
): void {
    $base = BASE_URL; // defined once in config/app.php

    $gtag_id        = 'G-HFQKVSMVW1';
    // $clarity_id     = 'sli6xhot4m';
    $bokun_uuid     = '9672d91a-bb8f-4062-9e06-a99a6c1325b4';

    $safe_title      = htmlspecialchars($title,       ENT_QUOTES, 'UTF-8');
    $safe_desc       = htmlspecialchars($description, ENT_QUOTES, 'UTF-8');
    $safe_body_class = htmlspecialchars($bodyClass,   ENT_QUOTES, 'UTF-8');
    $csrf_token      = htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8');

    // Build the canonical URL for this page.
    $canonical_base = SITE_URL;
    $request_uri    = $_SERVER['REQUEST_URI'] ?? '/';
    $path_only      = strtok($request_uri, '?');
    $web_path = '/' . ltrim(str_replace(rtrim($base, '/'), '', $path_only), '/');
    $web_path = preg_replace('/\.php$/', '', $web_path);
    $web_path = preg_replace('#/index$#', '/', $web_path);

    $canonical_url = htmlspecialchars($canonical_base . $web_path, ENT_QUOTES, 'UTF-8');
?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <!-- Primary Meta -->
        <title><?= $safe_title ?></title>
        <meta name="description" content="<?= $safe_desc ?>">
        <meta name="robots" content="index, follow">

        <!-- Canonical -->
        <link rel="canonical" href="<?= $canonical_url ?>">

        <!-- CSRF token — read by booking JS via document.head.dataset or meta tag -->
        <meta name="csrf-token" content="<?= $csrf_token ?>">

        <!-- Open Graph -->
        <meta property="og:type" content="website">
        <meta property="og:url" content="<?= $canonical_url ?>">
        <meta property="og:title" content="<?= $safe_title ?>">
        <meta property="og:description" content="<?= $safe_desc ?>">
        <meta property="og:site_name" content="QMAX Realty">
        <?php
        $og_img = $hero_image
            ? SITE_URL . '/' . htmlspecialchars(ltrim($hero_image, '/'), ENT_QUOTES, 'UTF-8')
            : SITE_URL . '/img/Logo550.webp';
        ?>
        <meta property="og:image" content="<?= $og_img ?>">
        <meta property="og:image:width"  content="550">
        <meta property="og:image:height" content="550">

        <!-- Twitter / X card -->
        <meta name="twitter:card"        content="summary_large_image">
        <meta name="twitter:title"       content="<?= $safe_title ?>">
        <meta name="twitter:description" content="<?= $safe_desc ?>">
        <meta name="twitter:image"       content="<?= $og_img ?>">

        <!-- Browser theme colour (matches emerald-600) -->
        <meta name="theme-color" content="#047857">

        <!-- Favicon -->
        <link rel="icon" type="image/webp" href="<?= $base ?>img/Logo200.webp">

        <!-- LCP preload -->
        <?php if ($hero_image): ?>
            <link rel="preload"
                as="image"
                href="<?= htmlspecialchars($hero_image, ENT_QUOTES, 'UTF-8') ?>"
                fetchpriority="high">
        <?php endif; ?>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link rel="stylesheet"
            href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap">

        <!-- Compiled Tailwind CSS -->
        <link rel="stylesheet" href="<?= $base ?>scss/main.css">

        <!-- Page transitions (Swup) + scroll reveal (SAL) -->
        <style>
            /* Swup: fade transition on the swapped container */

            /* SAL scroll reveal — base transition */
            [data-sal] {
                transition: opacity 0.6s ease, transform 0.6s ease;
                will-change: opacity, transform;
            }
            [data-sal="fade"] {
                opacity: 0;
            }
            [data-sal="fade"].sal-animate {
                opacity: 1;
            }
            [data-sal="slide-up"] {
                opacity: 0;
                transform: translateY(30px);
            }
            [data-sal="slide-up"].sal-animate {
                opacity: 1;
                transform: translateY(0);
            }
            [data-sal="slide-left"] {
                opacity: 0;
                transform: translateX(-30px);
            }
            [data-sal="slide-left"].sal-animate {
                opacity: 1;
                transform: translateX(0);
            }
            [data-sal="slide-right"] {
                opacity: 0;
                transform: translateX(30px);
            }
            [data-sal="slide-right"].sal-animate {
                opacity: 1;
                transform: translateX(0);
            }

            /* Respect users who prefer reduced motion */
            @media (prefers-reduced-motion: reduce) {
                html.is-changing .transition-fade,
                html.is-animating .transition-fade,
                [data-sal],
                [data-sal].sal-animate {
                    transition: none !important;
                    opacity: 1 !important;
                    transform: none !important;
                }
            }
        </style>

        <!-- Lucide icons (self-hosted via npm) -->
        <script src="<?= $base ?>js/lucide.min.js" defer></script>
        <script src="<?= $base ?>js/swup.min.js" defer></script>
        <script src="<?= $base ?>js/sal.min.js" defer></script>

        <!-- GTranslate language switcher -->
        <script src="https://cdn.gtranslate.net/widgets/latest/dropdown.js" defer></script>

        <!-- Google Analytics -->
        <script async src="https://www.googletagmanager.com/gtag/js?id=<?= htmlspecialchars($gtag_id, ENT_QUOTES, 'UTF-8') ?>"></script>
        <script>
            window.dataLayer = window.dataLayer || [];

            function gtag() {
                dataLayer.push(arguments);
            }
            gtag('js', new Date());
            gtag('config', '<?= htmlspecialchars($gtag_id, ENT_QUOTES, 'UTF-8') ?>');
        </script>

        <!-- Microsoft Clarity -->
        <!-- <script>
            (function(c, l, a, r, i, t, y) {
                c[a] = c[a] || function() {
                    (c[a].q = c[a].q || []).push(arguments)
                };
                t = l.createElement(r);
                t.async = 1;
                t.src = "https://www.clarity.ms/tag/" + i;
                y = l.getElementsByTagName(r)[0];
                y.parentNode.insertBefore(t, y);
            })(window, document, "clarity", "script", "<questionmark= htmlspecialchars($clarity_id, ENT_QUOTES, 'UTF-8') ?>");
        </script> -->

    </head>

    <body class="<?= $safe_body_class ?>">
    <?php
}

/**
 * Outputs </body></html> with shared JS.
 * lucide.createIcons() is called here — do not call it anywhere else.
 */
function qmx_foot(): void
{
    $base = BASE_URL;
    ?>
        <script src="<?= $base ?>js/main.js"></script>
        <script>
            (function initLucide() {
                function create() {
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                }
                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', create);
                } else {
                    create();
                }
            })();
        </script>

        <!--Start of Tawk.to Script-->
        <!-- <script type="text/javascript">
        var Tawk_API=Tawk_API||{}, Tawk_LoadStart=new Date();
        (function(){
        var s1=document.createElement("script"),s0=document.getElementsByTagName("script")[0];
        s1.async=true;
        s1.src='https://embed.tawk.to/686d32f2311a0d191792bc62/1ivl8u1jn';
        s1.charset='UTF-8';
        s1.setAttribute('crossorigin','*');
        s0.parentNode.insertBefore(s1,s0);
        })();
        </script> -->
        <!--End of Tawk.to Script-->

    </body>

    </html>
<?php
}