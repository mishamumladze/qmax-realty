<?php declare(strict_types=1); ?>
<footer class="bg-white border-t border-gray-200 my-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="flex flex-col md:flex-row justify-between items-center gap-8">

            <!-- Logo -->
            <div class="logo-container">
                <a href="<?= BASE_URL ?>index" aria-label="QMAX Realty homepage">
                    <img src="<?= BASE_URL ?>img/Logo550.webp" alt="QMAX Realty Logo"
                         class="w-1/2 max-w-xs mx-auto md:mx-0" width="200" height="80" loading="lazy">
                </a>
            </div>

            <!-- Contact Info -->
            <div class="contact-info mb-8 md:mb-0">
                <p class="font-bold text-lg mb-4">Contact Us</p>
                <p class="mb-2"><a href="<?= CONTACT_WA_ENG ?>" class="flex hover:text-emerald-600 transition-colors"><img src="<?= BASE_URL ?>img/Logos/whatsapp.png" class="w-6 h-6"> <?= CONTACT_PHONE_ENG ?> (ENG)</a></p>
                <p class="mb-2"><a href="<?= CONTACT_WA_RUS ?>" class="flex hover:text-emerald-600 transition-colors"><img src="<?= BASE_URL ?>img/Logos/whatsapp.png" class="w-6 h-6"> <?= CONTACT_PHONE_RUS ?> (RUS)</a></p>
                <p class="mb-2"><a href="" class="flex hover:text-emerald-600 transition-colors">📍 <?= CONTACT_ADDRESS ?></a></p>
                <p class="mb-2"><a href="mailto:<?= CONTACT_EMAIL ?>" class="flex hover:text-emerald-600 transition-colors">✉ <?= CONTACT_EMAIL ?></a></p>
            </div>

            <!-- Social Links -->
            <div class="social-links flex md:justify-end justify-center space-x-4 items-center">
                <a href="<?= CONTACT_WA_RUS ?>" target="_blank" rel="noopener noreferrer"
                   title="WhatsApp" aria-label="Message us on WhatsApp">
                    <img src="<?= BASE_URL ?>img/Logos/whatsapp.png" alt="WhatsApp" class="w-10 h-10 hover:opacity-80 transition-opacity duration-200">
                </a>
                <a href="https://t.me/giorgi_gugunishvili" target="_blank" rel="noopener noreferrer"
                   title="Telegram" aria-label="Message us on Telegram">
                    <img src="<?= BASE_URL ?>img/Logos/telegram.png" alt="Telegram" class="w-10 h-10 hover:opacity-80 transition-opacity duration-200">
                </a>
                <a href="viber://chat?number=<?= CONTACT_PHONE_RUS ?>" target="_blank" rel="noopener noreferrer"
                   title="Viber" aria-label="Message us on Viber">
                    <img src="<?= BASE_URL ?>img/Logos/viber.png" alt="Viber" class="w-10 h-10 hover:opacity-80 transition-opacity duration-200">
                </a>
            </div>
        </div>

        <!-- Map -->
        <div class="map-container mt-8 mb-8">
            <iframe
                title="QMAX Realty Office Location"
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2106.6914056829796!2d44.80410697709719!3d41.6922094431659!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x40440da98846d219%3A0x68326d9a16a639da!2sQMAX%20Realty!5e0!3m2!1sen!2sge!4v1739093736716!5m2!1sen!2sge"
                class="w-full h-80 border-0 rounded-lg"
                allowfullscreen
                loading="lazy"
                referrerpolicy="no-referrer-when-downgrade">
            </iframe>
        </div>

        <!-- Bottom Bar -->
        <div class="border-t border-gray-200 pt-6 text-center text-sm text-gray-500">
            <p>&copy; <?= date('Y') ?> QMAX Realty. All rights reserved. Tbilisi, Georgia.</p>
        </div>
    </div>
</footer>