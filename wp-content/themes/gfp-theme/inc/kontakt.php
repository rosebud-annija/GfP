<?php
defined('ABSPATH') || exit;

// ─── Kontaktseite: Meta Box ───────────────────────────────────────────────────

add_action('add_meta_boxes_page', function (WP_Post $post): void {
    if (get_page_template_slug($post->ID) !== 'page-kontakt.php') return;
    add_meta_box(
        'gfp_kontakt_meta',
        '📞 Kontaktseiten-Inhalte',
        'gfp_render_kontakt_meta_box',
        'page', 'normal', 'high'
    );
});

function gfp_render_kontakt_meta_box(WP_Post $post): void {
    wp_nonce_field('gfp_kontakt_save', 'gfp_kontakt_nonce');
    $f = fn(string $key, string $default = '') => esc_attr(get_post_meta($post->ID, '_kp_' . $key, true) ?: $default);
    $t = fn(string $key) => esc_textarea(get_post_meta($post->ID, '_kp_' . $key, true) ?: '');
    ?>
    <style>
        .gfp-kp-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .gfp-kp-field { display: flex; flex-direction: column; gap: 4px; margin-bottom: 0; }
        .gfp-kp-field.full { grid-column: 1 / -1; }
        .gfp-kp-field label { font-weight: 600; font-size: 12px; text-transform: uppercase; letter-spacing: .05em; color: #555; }
        .gfp-kp-field input, .gfp-kp-field textarea { width: 100%; padding: 7px 10px; border: 1px solid #ddd; border-radius: 3px; font-size: 13px; }
        .gfp-kp-field textarea { resize: vertical; min-height: 80px; }
        .gfp-kp-sep { grid-column: 1 / -1; border: none; border-top: 1px solid #eee; margin: 4px 0; }
    </style>
    <div class="gfp-kp-grid">
        <div class="gfp-kp-field full">
            <label for="kp_headline">Seiten-Titel (Hero)</label>
            <input type="text" id="kp_headline" name="kp_headline" value="<?php echo $f('headline', 'Kontakt'); ?>">
        </div>
        <div class="gfp-kp-field full">
            <label for="kp_sub">Subline (Hero)</label>
            <input type="text" id="kp_sub" name="kp_sub" value="<?php echo $f('sub', 'Wir freuen uns von dir zu hören.'); ?>">
        </div>
        <hr class="gfp-kp-sep">
        <div class="gfp-kp-field full">
            <label for="kp_intro">Einleitung (optional)</label>
            <textarea id="kp_intro" name="kp_intro"><?php echo $t('intro'); ?></textarea>
        </div>
        <div class="gfp-kp-field full">
            <label for="kp_address">Adresse (eine Zeile pro Absatz)</label>
            <textarea id="kp_address" name="kp_address"><?php echo $t('address'); ?></textarea>
        </div>
        <div class="gfp-kp-field">
            <label for="kp_phone">Telefon</label>
            <input type="text" id="kp_phone" name="kp_phone" value="<?php echo $f('phone'); ?>">
        </div>
        <div class="gfp-kp-field">
            <label for="kp_email">E-Mail (für Kontaktformular + Anzeige)</label>
            <input type="email" id="kp_email" name="kp_email" value="<?php echo $f('email', 'info@gfp.de'); ?>">
        </div>
        <hr class="gfp-kp-sep">
        <div class="gfp-kp-field full">
            <label for="kp_form_heading">Formular-Überschrift</label>
            <input type="text" id="kp_form_heading" name="kp_form_heading" value="<?php echo $f('form_heading', 'Schreib uns'); ?>">
        </div>
        <div class="gfp-kp-field full">
            <label for="kp_map_embed">Google-Maps-Einbettcode (iframe, optional)</label>
            <textarea id="kp_map_embed" name="kp_map_embed" style="min-height:60px;font-family:monospace;font-size:11px;"><?php echo esc_textarea(get_post_meta($post->ID, '_kp_map_embed', true) ?: ''); ?></textarea>
        </div>
    </div>
    <?php
}

add_action('save_post_page', function (int $post_id): void {
    if (!isset($_POST['gfp_kontakt_nonce'])) return;
    if (!wp_verify_nonce($_POST['gfp_kontakt_nonce'], 'gfp_kontakt_save')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;
    if (get_page_template_slug($post_id) !== 'page-kontakt.php') return;

    foreach (['kp_headline', 'kp_sub', 'kp_phone', 'kp_form_heading'] as $field) {
        update_post_meta($post_id, '_' . $field, sanitize_text_field(wp_unslash($_POST[$field] ?? '')));
    }
    foreach (['kp_intro', 'kp_address'] as $field) {
        update_post_meta($post_id, '_' . $field, sanitize_textarea_field(wp_unslash($_POST[$field] ?? '')));
    }
    update_post_meta($post_id, '_kp_email', sanitize_email(wp_unslash($_POST['kp_email'] ?? '')));

    $map_raw = wp_unslash($_POST['kp_map_embed'] ?? '');
    update_post_meta($post_id, '_kp_map_embed', wp_kses($map_raw, [
        'iframe' => ['src' => [], 'width' => [], 'height' => [], 'style' => [],
                     'allowfullscreen' => [], 'loading' => [], 'referrerpolicy' => [],
                     'frameborder' => [], 'title' => []],
    ]));
});

// ─── Kontaktformular-Handler ──────────────────────────────────────────────────

add_action('admin_post_nopriv_gfp_contact_form', 'gfp_handle_contact_form');
add_action('admin_post_gfp_contact_form',        'gfp_handle_contact_form');

function gfp_handle_contact_form(): void {
    if (empty($_POST['gfp_contact_nonce']) || !wp_verify_nonce($_POST['gfp_contact_nonce'], 'gfp_contact_send')) {
        wp_die('Ungültige Anfrage.', 403);
    }

    $page_id  = (int) ($_POST['_page_id'] ?? 0);
    $redirect = $page_id ? get_permalink($page_id) : home_url('/kontakt/');

    $name    = sanitize_text_field(wp_unslash($_POST['cf_name']        ?? ''));
    $email   = sanitize_email(wp_unslash($_POST['cf_email']            ?? ''));
    $subject = sanitize_text_field(wp_unslash($_POST['cf_subject']     ?? ''));
    $message = sanitize_textarea_field(wp_unslash($_POST['cf_message'] ?? ''));

    if (!$name || !$email || !$message || !is_email($email)) {
        wp_redirect(add_query_arg('error', '1', $redirect));
        exit;
    }

    $to      = get_post_meta($page_id, '_kp_email', true) ?: get_option('admin_email');
    $subject = $subject ?: 'Kontaktanfrage von ' . $name;
    $body    = "Name: $name\nE-Mail: $email\n\n$message";
    $headers = ['Content-Type: text/plain; charset=UTF-8', "Reply-To: $name <$email>"];

    $sent = wp_mail($to, $subject, $body, $headers);

    wp_redirect(add_query_arg($sent ? 'sent' : 'error', '1', $redirect));
    exit;
}
