<?php
/**
 * Template Name: Kontaktseite
 *
 * GfP — Kontaktseite
 * WordPress-Seite anlegen, Slug "kontakt", Template "Kontaktseite" wählen.
 * Inhalte (Adresse, E-Mail, Titel …) im Backend unter "Kontaktseiten-Inhalte" bearbeiten.
 */

defined('ABSPATH') || exit;

$headline     = gfp_kp('headline',     'Kontakt');
$sub          = gfp_kp('sub',          'Wir freuen uns von dir zu hören.');
$intro        = gfp_kp('intro',        '');
$address      = gfp_kp('address',      '');
$phone        = gfp_kp('phone',        '');
$email        = gfp_kp('email',        'info@gfp.de');
$form_heading = gfp_kp('form_heading', 'Schreib uns');
$map_embed    = get_post_meta(get_queried_object_id(), '_kp_map_embed', true) ?: '';

$sent  = isset($_GET['sent'])  && $_GET['sent']  === '1';
$error = isset($_GET['error']) && $_GET['error'] === '1';

get_header();

// ── Schema.org: LocalBusiness (Kontaktseite) + FAQPage ───────────────────────
$schema_kontakt = [
    '@context' => 'https://schema.org',
    '@graph'   => [],
];
if ($address || $phone || $email) {
    $schema_kontakt['@graph'][] = array_filter([
        '@type'   => 'LocalBusiness',
        '@id'     => home_url('/#organization'),
        'name'    => 'GfP – Gesellschaft für Personalentwicklung',
        'url'     => home_url('/'),
        'email'   => $email ?: null,
        'telephone' => $phone ?: null,
        'address' => $address ? [
            '@type'          => 'PostalAddress',
            'streetAddress'  => $address,
            'addressCountry' => 'AT',
        ] : null,
    ], fn($v) => $v !== null && $v !== '');
}
$schema_kontakt['@graph'][] = [
    '@type'      => 'FAQPage',
    'mainEntity' => [
        [
            '@type'          => 'Question',
            'name'           => 'Für wen sind die GfP-Programme geeignet?',
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Unsere Programme richten sich an Führungskräfte, Teams und Organisationen, die sich in den Bereichen Leadership, Facilitation, Teamentwicklung und Transformation weiterentwickeln möchten.'],
        ],
        [
            '@type'          => 'Question',
            'name'           => 'Wie kann ich mich für ein Seminar anmelden?',
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Anmeldungen sind direkt über die jeweilige Kursseite oder per E-Mail möglich. Wir beraten euch gerne bei der Auswahl des passenden Formats.'],
        ],
        [
            '@type'          => 'Question',
            'name'           => 'Gibt es auch Inhouse-Programme?',
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Ja, alle GfP-Programme sind auch als maßgeschneiderte Inhouse-Trainings direkt in eurer Organisation buchbar.'],
        ],
    ],
];
echo '<script type="application/ld+json">' . wp_json_encode($schema_kontakt, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>' . "\n";
?>

<div class="kontakt-page" style="padding-top: 64px;">

    <!-- Hero -->
    <section class="kontakt-hero">
        <div class="kontakt-hero__inner">
            <h1 class="kontakt-hero__title"><?php echo esc_html($headline); ?></h1>
            <p class="kontakt-hero__sub"><?php echo esc_html($sub); ?></p>
        </div>
    </section>

    <!-- Layout: Info links | Formular rechts -->
    <div class="kontakt-layout">

        <!-- Info-Spalte -->
        <div class="kontakt-info">
            <?php if ($intro) : ?>
                <p class="kontakt-intro"><?php echo nl2br(esc_html($intro)); ?></p>
            <?php endif; ?>

            <div class="kontakt-details">
                <?php if ($address) : ?>
                    <div class="kontakt-detail-item">
                        <span class="kontakt-detail-label">Adresse</span>
                        <p class="kontakt-detail-value"><?php echo nl2br(esc_html($address)); ?></p>
                    </div>
                <?php endif; ?>

                <?php if ($phone) : ?>
                    <div class="kontakt-detail-item">
                        <span class="kontakt-detail-label">Telefon</span>
                        <a href="tel:<?php echo esc_attr(preg_replace('/[\s\-]/', '', $phone)); ?>"
                           class="kontakt-detail-value kontakt-detail-link">
                            <?php echo esc_html($phone); ?>
                        </a>
                    </div>
                <?php endif; ?>

                <?php if ($email) : ?>
                    <div class="kontakt-detail-item">
                        <span class="kontakt-detail-label">E-Mail</span>
                        <a href="mailto:<?php echo esc_attr($email); ?>"
                           class="kontakt-detail-value kontakt-detail-link">
                            <?php echo esc_html($email); ?>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Formular-Spalte -->
        <div class="kontakt-form-wrap">
            <h2 class="kontakt-form-heading"><?php echo esc_html($form_heading); ?></h2>

            <?php if ($sent) : ?>
                <div class="kontakt-msg kontakt-msg--success">
                    Deine Nachricht wurde gesendet. Wir melden uns bald bei dir.
                </div>
            <?php elseif ($error) : ?>
                <div class="kontakt-msg kontakt-msg--error">
                    Beim Senden ist ein Fehler aufgetreten. Bitte prüfe deine Angaben und versuche es erneut.
                </div>
            <?php endif; ?>

            <form class="kontakt-form"
                  method="post"
                  action="<?php echo esc_url(admin_url('admin-post.php')); ?>">

                <input type="hidden" name="action"   value="gfp_contact_form">
                <input type="hidden" name="_page_id" value="<?php echo esc_attr(get_queried_object_id()); ?>">
                <?php wp_nonce_field('gfp_contact_send', 'gfp_contact_nonce'); ?>

                <div class="kontakt-field">
                    <label for="cf_name">Name *</label>
                    <input type="text" id="cf_name" name="cf_name"
                           value="<?php echo isset($_GET['sent']) ? '' : esc_attr($_POST['cf_name'] ?? ''); ?>"
                           required placeholder="Dein vollständiger Name">
                </div>

                <div class="kontakt-field">
                    <label for="cf_email">E-Mail *</label>
                    <input type="email" id="cf_email" name="cf_email"
                           value="<?php echo isset($_GET['sent']) ? '' : esc_attr($_POST['cf_email'] ?? ''); ?>"
                           required placeholder="deine@email.de">
                </div>

                <div class="kontakt-field">
                    <label for="cf_subject">Betreff</label>
                    <input type="text" id="cf_subject" name="cf_subject"
                           value="<?php echo isset($_GET['sent']) ? '' : esc_attr($_POST['cf_subject'] ?? ''); ?>"
                           placeholder="Worum geht es?">
                </div>

                <div class="kontakt-field">
                    <label for="cf_message">Nachricht *</label>
                    <textarea id="cf_message" name="cf_message"
                              rows="6" required
                              placeholder="Deine Nachricht an uns…"><?php echo isset($_GET['sent']) ? '' : esc_textarea($_POST['cf_message'] ?? ''); ?></textarea>
                </div>

                <button type="submit" class="kontakt-submit">Nachricht senden →</button>
            </form>
        </div>

    </div>

    <?php if ($map_embed) : ?>
        <div class="kontakt-map">
            <?php echo wp_kses($map_embed, [
                'iframe' => ['src' => [], 'width' => [], 'height' => [], 'style' => [],
                             'allowfullscreen' => [], 'loading' => [], 'referrerpolicy' => [],
                             'frameborder' => [], 'title' => []],
            ]); ?>
        </div>
    <?php endif; ?>

</div>

<?php get_footer(); ?>
