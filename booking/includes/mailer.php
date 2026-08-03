<?php
/**
 * QMAX Realty — Mailer
 *
 * Provides two public functions:
 *   qmx_send_inquiry_confirmation(array $inquiry) — customer-facing confirmation
 *   qmx_send_inquiry_notification(array $inquiry) — internal team alert
 *
 * Both called by qmx_send_inquiry_confirmation(); the team alert is
 * always fired first so a delivery failure on the customer side doesn't
 * swallow the lead.
 *
 * Prefers PHPMailer (vendor/autoload.php) and falls back to mail().
 */
declare(strict_types=1);

require_once __DIR__ . '/../config/booking.php';
require_once __DIR__ . '/../../config/app.php'; // CONTACT_* constants

// ── Shared PHPMailer factory ──────────────────────────────────────────────────
function qmx_make_mailer(): ?\PHPMailer\PHPMailer\PHPMailer
{
    $autoload = __DIR__ . '/../../vendor/autoload.php';
    if (!file_exists($autoload)) return null;
    require_once $autoload;
    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
    $mail->isSMTP();
    $mail->Host       = SMTP_HOST;
    $mail->Port       = SMTP_PORT;
    $mail->SMTPSecure = SMTP_ENCRYPTION === 'tls'
        ? \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS
        : \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
    $mail->SMTPAuth   = true;
    $mail->Username   = SMTP_USER;
    $mail->Password   = SMTP_PASS;
    $mail->CharSet    = 'UTF-8';
    $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
    return $mail;
}

// ── Send helper (html + plaintext, single recipient) ─────────────────────────
function qmx_dispatch_email(
    string $to_email,
    string $to_name,
    string $subject,
    string $html,
    string $text,
    bool   $bcc_team = false
): void {
    $mailer = qmx_make_mailer();
    if ($mailer) {
        $mailer->addAddress($to_email, $to_name);
        if ($bcc_team && defined('MAIL_BCC') && MAIL_BCC) {
            $mailer->addBCC(MAIL_BCC);
        }
        $mailer->isHTML(true);
        $mailer->Subject = $subject;
        $mailer->Body    = $html;
        $mailer->AltBody = $text;
        $mailer->send();
        return;
    }
    // Fallback: PHP mail()
    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= 'From: ' . MAIL_FROM_NAME . ' <' . MAIL_FROM . ">\r\n";
    if ($bcc_team && defined('MAIL_BCC') && MAIL_BCC) {
        $headers .= 'Bcc: ' . MAIL_BCC . "\r\n";
    }
    $sent = mail($to_email, $subject, $html, $headers);
    if (!$sent) {
        error_log('[QMX Mailer] mail() returned false → ' . $to_email . '. Install PHPMailer for reliable delivery.');
    }
}

// ── Public API ────────────────────────────────────────────────────────────────

/**
 * Send the customer-facing confirmation email + internal team alert.
 * Call this after qmx_create_inquiry() returns.
 */
function qmx_send_inquiry_confirmation(array $inquiry): void
{
    // Internal alert first — so a customer-email failure doesn't lose the lead
    qmx_send_inquiry_notification($inquiry);

    $to_email = $inquiry['email'];
    $to_name  = trim($inquiry['first_name'] . ' ' . $inquiry['last_name']);
    $ref      = $inquiry['reference'];
    $subject  = 'Your QMAX Realty Inquiry — ' . $ref;

    qmx_dispatch_email(
        $to_email,
        $to_name,
        $subject,
        qmx_inquiry_confirmation_html($inquiry),
        qmx_inquiry_confirmation_text($inquiry),
        bcc_team: false   // BCC already on the notification
    );
}

/**
 * Internal team notification — sent to MAIL_BCC or CONTACT_EMAIL.
 */
function qmx_send_inquiry_notification(array $inquiry): void
{
    $to      = (defined('MAIL_BCC') && MAIL_BCC) ? MAIL_BCC : CONTACT_EMAIL;
    $ref     = $inquiry['reference'];
    $type    = ucfirst($inquiry['inquiry_type'] ?? 'viewing');
    $subject = "🏠 New {$type} Request [{$ref}] — " . $inquiry['property_title'];

    try {
        qmx_dispatch_email(
            $to,
            'QMAX Realty Team',
            $subject,
            qmx_inquiry_notification_html($inquiry),
            qmx_inquiry_notification_text($inquiry),
            bcc_team: false
        );
    } catch (Throwable $e) {
        error_log('[QMX Mailer] Team notification failed for ' . $ref . ': ' . $e->getMessage());
    }
}

// ── Email templates ───────────────────────────────────────────────────────────

function qmx_inquiry_confirmation_html(array $b): string
{
    $ref      = htmlspecialchars($b['reference'],                ENT_QUOTES, 'UTF-8');
    $name     = htmlspecialchars($b['first_name'] . ' ' . $b['last_name'], ENT_QUOTES, 'UTF-8');
    $property = htmlspecialchars($b['property_title'],           ENT_QUOTES, 'UTF-8');
    $type     = htmlspecialchars(ucfirst($b['inquiry_type'] ?? 'viewing'), ENT_QUOTES, 'UTF-8');
    $lang     = htmlspecialchars($b['language'] ?? 'English',   ENT_QUOTES, 'UTF-8');
    $notes    = htmlspecialchars($b['notes'] ?? '',              ENT_QUOTES, 'UTF-8');
    $c_email  = htmlspecialchars(CONTACT_EMAIL,                  ENT_QUOTES, 'UTF-8');
    $c_wa = htmlspecialchars(CONTACT_WA,                 ENT_QUOTES, 'UTF-8');
    $c_ph = htmlspecialchars(CONTACT_PHONE,              ENT_QUOTES, 'UTF-8');

    // Viewing date row (only shown when a date was provided)
    $date_row = '';
    if (!empty($b['viewing_date'])) {
        $date_fmt  = htmlspecialchars(
            date('l, F j, Y', strtotime($b['viewing_date'])),
            ENT_QUOTES, 'UTF-8'
        );
        $time_slot = $b['viewing_time'] ? ucfirst(htmlspecialchars($b['viewing_time'], ENT_QUOTES, 'UTF-8')) : 'Any time';
        $date_row  = "
      <tr>
        <td style='font-size:13px;color:#6b7280;width:160px;padding:6px 0;'>📅 Preferred Date</td>
        <td style='font-size:14px;color:#111827;font-weight:600;padding:6px 0;'>{$date_fmt} &mdash; {$time_slot}</td>
      </tr>";
    }

    $notes_row = $notes ? "
      <tr>
        <td style='font-size:13px;color:#6b7280;padding:6px 0;'>📝 Your Message</td>
        <td style='font-size:14px;color:#374151;padding:6px 0;'>{$notes}</td>
      </tr>" : '';

    return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Inquiry Confirmed &mdash; {$ref}</title>
</head>
<body style="margin:0;padding:0;background:#f3f4f6;font-family:Arial,sans-serif;">
  <table width="100%" cellpadding="0" cellspacing="0" style="background:#f3f4f6;padding:32px 16px;">
    <tr><td align="center">
      <table width="560" cellpadding="0" cellspacing="0"
             style="background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.08);">

        <!-- Header -->
        <tr>
          <td style="background:#047857;padding:28px 32px;">
            <h1 style="margin:0;color:#ffffff;font-size:22px;font-weight:700;">
              🏠 Inquiry Received!
            </h1>
            <p style="margin:6px 0 0;color:#a7f3d0;font-size:13px;">Reference: <strong>{$ref}</strong></p>
          </td>
        </tr>

        <!-- Greeting -->
        <tr>
          <td style="padding:28px 32px 0;">
            <p style="margin:0;font-size:15px;color:#111827;">
              Hi <strong>{$name}</strong>,
            </p>
            <p style="margin:10px 0 0;font-size:14px;color:#374151;line-height:1.6;">
              Thank you for your interest in a property from QMAX Realty.
              We have received your <strong>{$type}</strong> request and
              one of our agents will contact you within <strong>24 hours</strong>.
            </p>
          </td>
        </tr>

        <!-- Inquiry Summary -->
        <tr>
          <td style="padding:24px 32px;">
            <table width="100%" cellpadding="0" cellspacing="0"
                   style="background:#f9fafb;border-radius:8px;border:1px solid #e5e7eb;padding:16px;">
              <tr>
                <td colspan="2" style="font-size:11px;color:#6b7280;font-weight:700;
                                       text-transform:uppercase;letter-spacing:.05em;
                                       padding-bottom:10px;border-bottom:1px solid #e5e7eb;">
                  Inquiry Summary
                </td>
              </tr>
              <tr>
                <td style="font-size:13px;color:#6b7280;width:160px;padding:10px 0 6px;">🏡 Property</td>
                <td style="font-size:14px;color:#111827;font-weight:700;padding:10px 0 6px;">{$property}</td>
              </tr>
              <tr>
                <td style="font-size:13px;color:#6b7280;padding:6px 0;">📋 Request Type</td>
                <td style="font-size:14px;color:#111827;font-weight:600;padding:6px 0;">{$type}</td>
              </tr>
              {$date_row}
              <tr>
                <td style="font-size:13px;color:#6b7280;padding:6px 0;">🌐 Language</td>
                <td style="font-size:14px;color:#111827;padding:6px 0;">{$lang}</td>
              </tr>
              {$notes_row}
            </table>
          </td>
        </tr>

        <!-- Next Steps -->
        <tr>
          <td style="padding:0 32px 24px;">
            <p style="margin:0 0 8px;font-size:14px;font-weight:700;color:#111827;">What happens next?</p>
            <ol style="margin:0;padding-left:20px;font-size:13px;color:#374151;line-height:1.8;">
              <li>Our agent will review your inquiry and reach out within 24 hours.</li>
              <li>We'll confirm availability and arrange a convenient viewing time.</li>
              <li>You'll receive a calendar invite with the confirmed details.</li>
            </ol>
          </td>
        </tr>

        <!-- Contact Block -->
        <tr>
          <td style="padding:0 32px 28px;">
            <p style="margin:0 0 8px;font-size:14px;font-weight:700;color:#111827;">Prefer to reach us directly?</p>
            <table cellpadding="0" cellspacing="0">
              <tr>
                <td style="padding-right:20px;">
                  <a href="mailto:{$c_email}"
                     style="font-size:13px;color:#047857;text-decoration:none;">✉️ {$c_email}</a>
                </td>
              </tr>
              <tr><td style="padding-top:6px;">
                <a href="{$c_wa}"
                   style="font-size:13px;color:#047857;text-decoration:none;">
                  💬 WhatsApp: {$c_ph}
                </a>
              </td></tr>
            </table>
          </td>
        </tr>

        <!-- Footer -->
        <tr>
          <td style="background:#f9fafb;padding:16px 32px;border-top:1px solid #e5e7eb;">
            <p style="margin:0;font-size:12px;color:#9ca3af;">
              Inquiry reference: <strong>{$ref}</strong> &middot; QMAX Realty &middot;
              <a href="mailto:{$c_email}" style="color:#9ca3af;">{$c_email}</a>
            </p>
          </td>
        </tr>

      </table>
    </td></tr>
  </table>
</body>
</html>
HTML;
}

function qmx_inquiry_confirmation_text(array $b): string
{
    $ref       = $b['reference'];
    $name      = trim($b['first_name'] . ' ' . $b['last_name']);
    $type      = ucfirst($b['inquiry_type'] ?? 'viewing');
    $prop      = $b['property_title'];
    $lang      = $b['language'] ?? 'English';
    $c_email   = CONTACT_EMAIL;
    $c_ph  = CONTACT_PHONE;

    $date_line = !empty($b['viewing_date'])
        ? 'Preferred Date: ' . date('l, F j, Y', strtotime($b['viewing_date']))
            . ($b['viewing_time'] ? ' (' . ucfirst($b['viewing_time']) . ')' : '') . "\n"
        : '';

    $notes_line = !empty($b['notes']) ? 'Message: ' . $b['notes'] . "\n" : '';

    return <<<TEXT
Hi {$name},

Thank you for your inquiry with QMAX Realty.
We'll be in touch within 24 hours.

── Inquiry Summary ──────────────────────────
Reference:    {$ref}
Property:     {$prop}
Request Type: {$type}
{$date_line}Language:     {$lang}
{$notes_line}
── What happens next? ───────────────────────
1. Our agent will review your inquiry and reach out within 24 hours.
2. We'll confirm availability and arrange a convenient viewing time.
3. You'll receive a calendar invite with the confirmed details.

── Contact Us ───────────────────────────────
Email:           {$c_email}
WhatsApp:  {$c_ph}

QMAX Realty
TEXT;
}

function qmx_inquiry_notification_html(array $b): string
{
    $ref      = htmlspecialchars($b['reference'],                ENT_QUOTES, 'UTF-8');
    $name     = htmlspecialchars($b['first_name'] . ' ' . $b['last_name'], ENT_QUOTES, 'UTF-8');
    $property = htmlspecialchars($b['property_title'],           ENT_QUOTES, 'UTF-8');
    $type     = htmlspecialchars(ucfirst($b['inquiry_type'] ?? 'viewing'), ENT_QUOTES, 'UTF-8');
    $email    = htmlspecialchars($b['email'],                    ENT_QUOTES, 'UTF-8');
    $phone    = htmlspecialchars($b['phone'],                    ENT_QUOTES, 'UTF-8');
    $lang     = htmlspecialchars($b['language'] ?? 'English',   ENT_QUOTES, 'UTF-8');
    $flag     = ($lang === 'Russian') ? '🇷🇺' : '🇬🇧';
    $notes    = htmlspecialchars($b['notes'] ?? '',              ENT_QUOTES, 'UTF-8');
    $ip       = htmlspecialchars($b['ip'] ?? 'N/A',             ENT_QUOTES, 'UTF-8');

    $date_row = '';
    if (!empty($b['viewing_date'])) {
        $date_fmt  = htmlspecialchars(
            date('l, F j, Y', strtotime($b['viewing_date'])),
            ENT_QUOTES, 'UTF-8'
        );
        $time_slot = $b['viewing_time']
            ? ucfirst(htmlspecialchars($b['viewing_time'], ENT_QUOTES, 'UTF-8'))
            : 'Any time';
        $date_row  = "
      <tr>
        <td style='font-size:13px;color:#6b7280;width:140px;padding:5px 0;'>📅 Preferred Date</td>
        <td style='font-size:14px;color:#111827;font-weight:600;padding:5px 0;'>{$date_fmt} &mdash; {$time_slot}</td>
      </tr>";
    }

    $notes_row = $notes ? "
      <tr>
        <td style='font-size:13px;color:#6b7280;padding:5px 0;'>📝 Notes</td>
        <td style='font-size:14px;color:#374151;padding:5px 0;'>{$notes}</td>
      </tr>" : '';

    return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"></head>
<body style="font-family:Arial,sans-serif;background:#f3f4f6;padding:24px;">
<table width="560" cellpadding="0" cellspacing="0"
       style="background:#fff;border-radius:10px;overflow:hidden;margin:auto;
              box-shadow:0 2px 8px rgba(0,0,0,.08);">

  <!-- Header -->
  <tr>
    <td style="background:#047857;padding:20px 28px;">
      <h1 style="margin:0;color:#fff;font-size:20px;">🏠 New {$type} Request</h1>
      <p style="margin:4px 0 0;color:#a7f3d0;font-size:13px;">{$ref}</p>
    </td>
  </tr>

  <!-- Body -->
  <tr>
    <td style="padding:24px 28px;">
      <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
          <td style="font-size:13px;color:#6b7280;width:140px;padding:5px 0;">🏡 Property</td>
          <td style="font-size:14px;color:#111827;font-weight:700;padding:5px 0;">{$property}</td>
        </tr>
        <tr>
          <td style="font-size:13px;color:#6b7280;padding:5px 0;">📋 Request Type</td>
          <td style="font-size:14px;color:#111827;font-weight:600;padding:5px 0;">{$type}</td>
        </tr>
        {$date_row}
        <tr>
          <td style="font-size:13px;color:#6b7280;padding:5px 0;">👤 Client</td>
          <td style="font-size:14px;color:#111827;font-weight:600;padding:5px 0;">{$name}</td>
        </tr>
        <tr>
          <td style="font-size:13px;color:#6b7280;padding:5px 0;">📧 Email</td>
          <td style="font-size:14px;padding:5px 0;">
            <a href="mailto:{$email}" style="color:#047857;">{$email}</a>
          </td>
        </tr>
        <tr>
          <td style="font-size:13px;color:#6b7280;padding:5px 0;">📱 Phone</td>
          <td style="font-size:14px;color:#111827;padding:5px 0;">
            <a href="tel:{$phone}" style="color:#047857;">{$phone}</a>
          </td>
        </tr>
        <tr>
          <td style="font-size:13px;color:#6b7280;padding:5px 0;">🌐 Language</td>
          <td style="font-size:14px;color:#111827;padding:5px 0;">{$flag} {$lang}</td>
        </tr>
        {$notes_row}
      </table>
    </td>
  </tr>

  <!-- Quick-reply row -->
  <tr>
    <td style="padding:0 28px 20px;">
      <a href="mailto:{$email}?subject=Re: Your QMAX Realty Inquiry [{$ref}]"
         style="display:inline-block;background:#047857;color:#fff;font-size:13px;
                font-weight:700;padding:10px 20px;border-radius:8px;text-decoration:none;">
        ✉️ Reply to Client
      </a>
    </td>
  </tr>

  <!-- Footer -->
  <tr>
    <td style="background:#f9fafb;padding:14px 28px;font-size:12px;
               color:#9ca3af;border-top:1px solid #e5e7eb;">
      Ref: {$ref} &middot; IP: {$ip} &middot; QMAX Realty Admin
    </td>
  </tr>

</table>
</body>
</html>
HTML;
}

function qmx_inquiry_notification_text(array $b): string
{
    $ref   = $b['reference'];
    $type  = ucfirst($b['inquiry_type'] ?? 'viewing');
    $name  = trim($b['first_name'] . ' ' . $b['last_name']);
    $prop  = $b['property_title'];
    $email = $b['email'];
    $phone = $b['phone'];
    $lang  = $b['language'] ?? 'English';

    $date_line = !empty($b['viewing_date'])
        ? 'Preferred Date: ' . date('l, F j, Y', strtotime($b['viewing_date']))
            . ($b['viewing_time'] ? ' (' . ucfirst($b['viewing_time']) . ')' : '') . "\n"
        : '';

    $notes_line = !empty($b['notes']) ? 'Notes: ' . $b['notes'] . "\n" : '';

    return <<<TEXT
New {$type} Request: {$ref}
Property:  {$prop}
Type:      {$type}
{$date_line}Client:    {$name}
Email:     {$email}
Phone:     {$phone}
Language:  {$lang}
{$notes_line}
TEXT;
}