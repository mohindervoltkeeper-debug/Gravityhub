<?php
$page_title = 'Contact Us | EnergyHub Services';
$page_description = 'Contact EnergyHub Services for device repair, installation, and technical support.';
$page_social_image = 'assets/images/photo11.1.webp';
$body_class = 'page-contact';

require_once __DIR__ . '/includes/service-data.php';
require_once __DIR__ . '/includes/form-handler.php';

$phone_countries = phone_country_options();
$form_state = create_form_state();
$selected_phone_country = $form_state['values']['phoneCountryCode'] ?? 'IN';
if (!isset($phone_countries[$selected_phone_country])) {
    $selected_phone_country = 'IN';
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form_state = form_submission_result('contact', $_POST, []);
    $selected_phone_country = $form_state['values']['phoneCountryCode'] ?? 'IN';
    if (!isset($phone_countries[$selected_phone_country])) {
        $selected_phone_country = 'IN';
    }
    if (!empty($form_state['saved'])) {
        $_SESSION['form_success'] = 'Message sent successfully.';
        $_SESSION['form_csrf_token'] = bin2hex(random_bytes(32));
        session_regenerate_id(true);
        header('Location: contact.php#contact-form', true, 303);
        exit;
    }
}

include 'includes/head.php';
include 'includes/header.php';
?>

<main id="main-content">
<!-- Page Hero -->
<section class="page-hero">
    <div class="hero-background" aria-hidden="true">
        <img class="hero-slide is-active" src="assets/images/photo11.1.webp" srcset="<?php echo webp_image_srcset('assets/images/photo11.1.webp'); ?>" alt="" aria-hidden="false" fetchpriority="high" decoding="async">
    </div>
    <div class="hero-bg-overlay" aria-hidden="true"></div>
    <div class="container hero-container">
        <div class="page-hero-content reveal">
            <h1>Contact Us</h1>
            <p>Tell us what equipment you need help with and how we can reach you. We will review your message and discuss the next step.</p>
            <div class="hero-cta">
                <a href="about.php" class="btn btn-outline btn-lg">Learn About Us</a>
            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="contact-grid">
            <!-- Contact Information -->
            <div class="contact-info-card reveal">
                <h2>Contact Information</h2>
                <p style="color: var(--color-text-muted); margin-bottom: var(--space-6);">Share a few details about your equipment or question so we can understand what support you are looking for.</p>

                <div class="contact-info-item reveal">
                    <i class="bi bi-telephone"></i>
                    <div>
                        <h4>Phone</h4>
                        <p><a href="tel:+919983498888">+91 99834 98888</a></p>
                    </div>
                </div>
                <div class="contact-info-item reveal">
                    <i class="bi bi-whatsapp"></i>
                    <div>
                        <h4>WhatsApp</h4>
                        <p><a href="https://wa.me/919983498888" target="_blank" rel="noopener">+91 99834 98888</a></p>
                    </div>
                </div>

                <div class="contact-info-item reveal">
                    <i class="bi bi-envelope"></i>
                    <div>
                        <h4>Email</h4>
                        <p><a href="mailto:gravityhubautomation@gmail.com">gravityhubautomation@gmail.com</a></p>
                    </div>
                </div>

                <div class="contact-info-item reveal">
                    <i class="bi bi-geo-alt"></i>
                    <div>
                        <h4>Address</h4>
                        <p>Service address and coverage area to be confirmed</p>
                    </div>
                </div>

                <div class="contact-info-item reveal">
                    <i class="bi bi-clock"></i>
                    <div>
                        <h4>Business Hours</h4>
                        <p>Please contact us to confirm availability</p>
                    </div>
                </div>

            </div>

            <!-- Contact Form -->
            <div class="contact-form-card reveal" id="contact-form">
                <h2>Send Us a Message</h2>
                <p style="color: var(--color-text-muted); margin-bottom: var(--space-6);">Describe the equipment, the issue or goal, and any useful context. We will review your message.</p>

                <?php if ($form_state['success'] !== ''): ?>
                <div class="form-success-toast" data-success-toast role="status" aria-live="polite"><?php echo htmlspecialchars($form_state['success'], ENT_QUOTES, 'UTF-8'); ?></div>
                <?php endif; ?>
                <?php if (!empty($form_state['errors']['general'])): ?>
                <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($form_state['errors']['general'], ENT_QUOTES, 'UTF-8'); ?></div>
                <?php endif; ?>

                <form data-validate id="contactForm" method="post" novalidate>
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($form_state['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control<?php echo isset($form_state['errors']['name']) ? ' is-invalid' : ''; ?>" id="name" name="name" placeholder="Your name" maxlength="150" value="<?php echo escape_form_value($form_state, 'name'); ?>" required>
                            <div class="invalid-feedback"><?php echo form_field_error($form_state, 'name', 'Please enter your name.'); ?></div>
                        </div>
                        <div class="form-group">
                            <label for="email">Email Address <span class="text-danger">*</span></label>
                            <input type="email" class="form-control<?php echo isset($form_state['errors']['email']) ? ' is-invalid' : ''; ?>" id="email" name="email" placeholder="you@example.com" maxlength="254" value="<?php echo escape_form_value($form_state, 'email'); ?>" required>
                            <div class="invalid-feedback"><?php echo form_field_error($form_state, 'email', 'Please enter a valid email address.'); ?></div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="phone">Phone number (optional)</label>
                            <div class="phone-input-group">
                                <label class="visually-hidden" for="phoneCountryCode">Country calling code</label>
                                <select class="form-control<?php echo isset($form_state['errors']['phoneCountryCode']) ? ' is-invalid' : ''; ?>" id="phoneCountryCode" name="phoneCountryCode" data-phone-country required>
                                    <?php foreach ($phone_countries as $country_iso => $country): ?>
                                    <option value="<?php echo htmlspecialchars($country_iso, ENT_QUOTES, 'UTF-8'); ?>" data-dial-code="<?php echo htmlspecialchars($country['dial_code'], ENT_QUOTES, 'UTF-8'); ?>" data-min-digits="<?php echo (int) $country['min_digits']; ?>" data-max-digits="<?php echo (int) $country['max_digits']; ?>" data-leading-digits="<?php echo htmlspecialchars($country['leading_digits'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"<?php echo $selected_phone_country === $country_iso ? ' selected' : ''; ?>><?php echo htmlspecialchars($country['name'] . ' (' . $country['dial_code'] . ')', ENT_QUOTES, 'UTF-8'); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <input type="tel" inputmode="tel" autocomplete="tel-national" data-international-phone class="form-control<?php echo isset($form_state['errors']['phone']) || isset($form_state['errors']['phoneCountryCode']) ? ' is-invalid' : ''; ?>" id="phone" name="phone" placeholder="Phone number" maxlength="18" value="<?php echo escape_form_value($form_state, 'phone'); ?>">
                                <div class="invalid-feedback"><?php echo htmlspecialchars($form_state['errors']['phoneCountryCode'] ?? $form_state['errors']['phone'] ?? 'Enter a valid phone number for the selected country.', ENT_QUOTES, 'UTF-8'); ?></div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="subject">Subject</label>
                            <input type="text" class="form-control" id="subject" name="subject" placeholder="Device repair inquiry" maxlength="200" value="<?php echo escape_form_value($form_state, 'subject'); ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="message">Message <span class="text-danger">*</span></label>
                        <textarea class="form-control<?php echo isset($form_state['errors']['message']) ? ' is-invalid' : ''; ?>" id="message" name="message" rows="5" maxlength="5000" placeholder="Tell us about your device and the issue you are facing..." required><?php echo escape_form_value($form_state, 'message'); ?></textarea>
                        <div class="invalid-feedback"><?php echo form_field_error($form_state, 'message', 'Please describe your request.'); ?></div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg">Send Message</button>
                </form>
            </div>
        </div>

        <section class="contact-map-section reveal" aria-labelledby="contact-map-title">
            <h2 id="contact-map-title">Service Location</h2>
            <div class="service-location-map">
                <iframe
                    src="https://www.openstreetmap.org/export/embed.html?bbox=68.1%2C6.5%2C97.5%2C35.7&amp;layer=mapnik"
                    title="Map showing India"
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"
                    allowfullscreen>
                </iframe>
            </div>
            <p class="small text-muted">This map shows India for reference and does not mark a confirmed service address or coverage area. Contact us to confirm availability in your location. Map data © <a href="https://www.openstreetmap.org/copyright">OpenStreetMap contributors</a>.</p>
        </section>
    </div>
</section>
</main>

<?php include 'includes/footer.php'; ?>

<script src="assets/js/main.js?v=<?php echo filemtime(__DIR__ . '/assets/js/main.js'); ?>" defer></script>
</body>
</html>