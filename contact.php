<?php
declare(strict_types=1);

session_start();

// Regenerate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

require_once __DIR__ . '/includes/layout.php';

qmx_head(
    'Contact QMAX Realty — Get in Touch',
    'Contact QMAX Realty for expert guidance on buying, selling, or renting property in Georgia.'
);

if (!defined('CONTACT_EMAIL')) {
    require_once __DIR__ . '/config/app.php';
}

// ── Handle form submission ──────────────────────────────────────────────────
$success = false;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF check
    $submitted_token = trim($_POST['csrf_token'] ?? '');
    $expected_token = $_SESSION['csrf_token'] ?? '';
    
    if (empty($submitted_token) || empty($expected_token) || !hash_equals($expected_token, $submitted_token)) {
        $error = 'Invalid request. Please refresh and try again.';
    } else {
        // Rotate CSRF token
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        
        // Rate limiting (same as newsletter.php)
        $client_ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $rate_file = sys_get_temp_dir() . '/contact_' . hash('sha256', $client_ip) . '.tmp';
        $now = time();
        $last = file_exists($rate_file) ? (int)@file_get_contents($rate_file) : 0;
        
        if (($now - $last) < 60) {
            $error = 'Please wait a moment before sending another message.';
        } else {
            @file_put_contents($rate_file, (string)$now);
            
            // Validate
            $first_name = trim($_POST['first_name'] ?? '');
            $last_name = trim($_POST['last_name'] ?? '');
            $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
            $phone = trim($_POST['phone'] ?? '');
            $subject = trim($_POST['subject'] ?? '');
            $message = trim($_POST['message'] ?? '');
            
            if (empty($first_name) || empty($last_name) || $email === false || empty($subject) || empty($message)) {
                $error = 'Please fill in all required fields.';
            } else {
                // Save to database (same pattern as newsletter.php)
                try {
                    require_once __DIR__ . '/booking/includes/db.php';
                    $db = qmx_db();
                    
                    $db->exec("
                        CREATE TABLE IF NOT EXISTS contact_messages (
                            id INTEGER PRIMARY KEY AUTOINCREMENT,
                            first_name TEXT NOT NULL,
                            last_name TEXT NOT NULL,
                            email TEXT NOT NULL,
                            phone TEXT,
                            subject TEXT NOT NULL,
                            message TEXT NOT NULL,
                            ip TEXT,
                            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                        )
                    ");
                    
                    $stmt = $db->prepare("
                        INSERT INTO contact_messages (first_name, last_name, email, phone, subject, message, ip)
                        VALUES (?, ?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([$first_name, $last_name, $email, $phone, $subject, $message, $client_ip]);
                } catch (\Throwable $e) {
                    error_log('[Contact] DB error: ' . $e->getMessage());
                }
                
                // Send email (same as newsletter.php pattern)
                $to = CONTACT_EMAIL;
                $headers = "MIME-Version: 1.0\r\n";
                $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
                $headers .= "From: " . $to . "\r\n";
                $headers .= "Reply-To: " . $email . "\r\n";
                
                $body = "
                <h2>New Contact Form Submission</h2>
                <p><strong>Name:</strong> {$first_name} {$last_name}</p>
                <p><strong>Email:</strong> <a href='mailto:{$email}'>{$email}</a></p>
                <p><strong>Phone:</strong> " . ($phone ? $phone : 'Not provided') . "</p>
                <p><strong>Subject:</strong> {$subject}</p>
                <p><strong>Message:</strong><br>" . nl2br($message) . "</p>
                ";
                
                if (mail($to, "[QMAX Realty] Contact: {$subject}", $body, $headers)) {
                    $success = true;
                } else {
                    $error = 'Unable to send message. Please try again.';
                }
            }
        }
    }
}
?>

<?php require_once __DIR__ . '/includes/navbar.php'; ?>

<main id="swup" class="transition-fade min-h-screen container mx-auto px-4 md:py-18 py-0">

    <!-- Hero -->
    <section class="relative bg-gradient-to-r from-emerald-600 to-emerald-700 text-white py-16 md:py-24 rounded-2xl mb-12">
        <div class="relative container mx-auto px-4 text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-4">Get in Touch</h1>
            <p class="text-lg md:text-xl max-w-2xl mx-auto opacity-90">
                We're here to help you find your dream property in Georgia.
            </p>
        </div>
    </section>

    <?php if ($success): ?>
        <div class="bg-green-50 border border-green-200 text-green-700 rounded-xl px-6 py-4 mb-8 max-w-3xl mx-auto">
            <i data-lucide="check-circle" class="w-5 h-5 inline mr-2"></i>
            Thank you! We'll get back to you within 24 hours.
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl px-6 py-4 mb-8 max-w-3xl mx-auto">
            <i data-lucide="alert-circle" class="w-5 h-5 inline mr-2"></i>
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-5 gap-8 mb-16">

        <!-- Contact Info -->
        <div class="lg:col-span-2 space-y-4">
            <div class="bg-white rounded-2xl shadow-sm hover:shadow-md transition-shadow p-6 border border-gray-100">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-emerald-50 rounded-xl flex items-center justify-center flex-shrink-0">
                        <i data-lucide="phone" class="w-5 h-5 text-emerald-600"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Call us</p>
                        <a href="tel:<?= CONTACT_PHONE ?>" class="text-emerald-600 hover:text-emerald-700 font-medium block">
                            <?= CONTACT_PHONE ?>
                        </a>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm hover:shadow-md transition-shadow p-6 border border-gray-100">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-emerald-50 rounded-xl flex items-center justify-center flex-shrink-0">
                        <i data-lucide="mail" class="w-5 h-5 text-emerald-600"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Email us</p>
                        <a href="mailto:<?= CONTACT_EMAIL ?>" class="text-emerald-600 hover:text-emerald-700 font-medium">
                            <?= CONTACT_EMAIL ?>
                        </a>
                    </div>
                </div>
            </div>

            <!-- <div class="bg-white rounded-2xl shadow-sm hover:shadow-md transition-shadow p-6 border border-gray-100">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-emerald-50 rounded-xl flex items-center justify-center flex-shrink-0">
                        <i data-lucide="map-pin" class="w-5 h-5 text-emerald-600"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Visit us</p>
                        <p class="text-gray-700 font-medium"><questionmark= CONTACT_ADDRESS ?></p>
                    </div>
                </div>
            </div> -->

            <div class="bg-green-50 rounded-2xl p-6 border border-green-100">
                <div class="flex items-center gap-3 mb-3">
                    <img src="<?= BASE_URL ?>img/Logos/si-whatsapp.svg" alt="WhatsApp" class="w-8 h-8">
                    <div>
                        <p class="font-semibold text-gray-800">Chat on WhatsApp</p>
                        <p class="text-sm text-gray-600">Fast response</p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <a href="<?= CONTACT_WA ?>" target="_blank" rel="noopener noreferrer"
                       class="flex-1 bg-green-600 hover:bg-green-700 text-white text-center px-4 py-2.5 rounded-lg font-semibold text-sm transition-colors">
                        WhatsApp
                    </a>
                </div>
            </div>
        </div>

        <!-- Contact Form -->
        <div class="lg:col-span-3">
            <div class="bg-white rounded-2xl shadow-sm p-8 border border-gray-100">
                <h2 class="text-2xl font-bold text-gray-800 mb-1">Send a Message</h2>
                <p class="text-gray-500 mb-6">Fill in the form and we'll get back to you within 24 hours.</p>

                <form method="POST" action="<?= BASE_URL ?>contact.php">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="first_name" class="block text-sm font-medium text-gray-700 mb-1">First name *</label>
                            <input type="text" id="first_name" name="first_name" required
                                   class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition">
                        </div>
                        <div>
                            <label for="last_name" class="block text-sm font-medium text-gray-700 mb-1">Last name *</label>
                            <input type="text" id="last_name" name="last_name" required
                                   class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition">
                        </div>
                    </div>

                    <div class="mt-4">
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email address *</label>
                        <input type="email" id="email" name="email" required
                               class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition">
                    </div>

                    <div class="mt-4">
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone number</label>
                        <input type="tel" id="phone" name="phone"
                               class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition"
                               placeholder="+995 555 000 000">
                    </div>

                    <div class="mt-4">
                        <label for="subject" class="block text-sm font-medium text-gray-700 mb-1">Subject *</label>
                        <select id="subject" name="subject" required
                                class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition">
                            <option value="">Select a subject...</option>
                            <option value="buying"<?= (($_GET['subject'] ?? '') === 'buying') ? ' selected' : '' ?>>Buying a property</option>
                            <option value="selling"<?= (($_GET['subject'] ?? '') === 'selling') ? ' selected' : '' ?>>Selling a property</option>
                            <option value="renting"<?= (($_GET['subject'] ?? '') === 'renting') ? ' selected' : '' ?>>Renting a property</option>
                            <option value="valuation"<?= (($_GET['subject'] ?? '') === 'valuation') ? ' selected' : '' ?>>Property valuation</option>
                            <option value="investment"<?= (($_GET['subject'] ?? '') === 'investment') ? ' selected' : '' ?>>Investment inquiry</option>
                            <option value="general"<?= (($_GET['subject'] ?? '') === 'general') ? ' selected' : '' ?>>General question</option>
                        </select>
                    </div>

                    <div class="mt-4">
                        <label for="message" class="block text-sm font-medium text-gray-700 mb-1">Message *</label>
                        <textarea id="message" name="message" rows="5" required
                                  class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition resize-none"
                                  placeholder="Tell us about your real estate needs..."></textarea>
                    </div>

                    <button type="submit"
                            class="mt-6 w-full bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-3 px-6 rounded-xl transition-colors duration-200 flex items-center justify-center gap-2">
                        <i data-lucide="send" class="w-4 h-4"></i>
                        Send Message
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Newsletter -->
    <section class="bg-gray-50 rounded-2xl p-8 md:p-12 text-center mb-16">
        <h2 class="text-2xl font-bold text-gray-800 mb-2">Stay Updated</h2>
        <p class="text-gray-600 mb-6">Subscribe for the latest property listings and market insights.</p>
        <form method="POST" action="<?= BASE_URL ?>newsletter.php" class="flex flex-col sm:flex-row gap-3 max-w-md mx-auto">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
            <input type="email" name="email" required placeholder="Enter your email"
                   class="flex-1 border border-gray-300 rounded-lg px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition">
            <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-3 rounded-lg font-semibold text-sm transition-colors whitespace-nowrap">
                Subscribe
            </button>
        </form>
    </section>

</main>

<?php require_once __DIR__ . '/includes/scroll-top.php'; ?>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
<?php qmx_foot(); ?>