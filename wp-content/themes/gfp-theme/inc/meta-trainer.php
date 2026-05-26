<?php
defined('ABSPATH') || exit;

// ─── Trainer Meta Box ─────────────────────────────────────────────────────────

add_action('add_meta_boxes', function () {
    add_meta_box(
        'gfp_trainer_details',
        'Trainer-Details',
        'gfp_render_trainer_meta_box',
        'trainer',
        'normal',
        'high'
    );
});

function gfp_render_trainer_meta_box(WP_Post $post): void {
    wp_nonce_field('gfp_trainer_save', 'gfp_trainer_nonce');
    $role   = get_post_meta($post->ID, '_trainer_role',   true) ?: '';
    $bio    = get_post_meta($post->ID, '_trainer_bio',    true) ?: '';
    $tags   = get_post_meta($post->ID, '_trainer_tags',   true) ?: '';
    $gruppe     = get_post_meta($post->ID, '_trainer_gruppe',     true) ?: 'trainerin';
    $startseite = get_post_meta($post->ID, '_trainer_startseite', true);
    ?>
    <style>
    .gfp-tr .gfp-field { margin-bottom:18px; }
    .gfp-tr .gfp-field label { display:block; font-weight:600; font-size:13px; margin-bottom:5px; color:#333; }
    .gfp-tr .gfp-field .desc { font-size:12px; color:#888; margin-bottom:5px; }
    .gfp-tr .gfp-field input[type=text],
    .gfp-tr .gfp-field select,
    .gfp-tr .gfp-field textarea { width:100%; padding:8px 10px; border:1px solid #ccc; border-radius:4px; font-size:14px; box-sizing:border-box; }
    .gfp-tr .gfp-field textarea { min-height:90px; resize:vertical; }
    .gfp-tr .gfp-hint { background:#f0f7ff; border:1px solid #b8d4f0; border-radius:4px; padding:10px 14px; font-size:13px; color:#444; margin-bottom:20px; }
    .gfp-tr .gfp-sep  { border:none; border-top:2px solid #eee; margin:8px 0 18px; }
    .gfp-tr .gfp-toggle { display:flex; align-items:center; gap:10px; padding:10px 14px;
                          background:#f6fff0; border:1px solid #a8d88a; border-radius:4px; }
    .gfp-tr .gfp-toggle input[type=checkbox] { width:18px; height:18px; margin:0; cursor:pointer; accent-color:#2e7d32; }
    .gfp-tr .gfp-toggle label { margin:0; font-weight:600; font-size:13px; color:#2e7d32; cursor:pointer; }
    </style>
    <div class="gfp-tr">
        <p class="gfp-hint">📷 <strong>Foto:</strong> Bitte das Foto als <em>Beitragsbild</em> in der rechten Seitenleiste hochladen. Empfohlen: quadratisch, mind. 400×400 px.</p>

        <div class="gfp-field">
            <label for="trainer_gruppe">Gruppe (Über-uns-Seite)</label>
            <p class="desc">Bestimmt, in welchem Abschnitt die Person auf der Über-uns-Seite erscheint.</p>
            <select name="trainer_gruppe" id="trainer_gruppe">
                <option value="team"      <?php selected($gruppe, 'team');      ?>>Firmenmitglied</option>
                <option value="trainerin" <?php selected($gruppe, 'trainerin'); ?>>Trainerin / Trainer</option>
                <option value="netzwerk"  <?php selected($gruppe, 'netzwerk');  ?>>Netzwerkpartnerin / -partner</option>
            </select>
        </div>

        <div class="gfp-field">
            <div class="gfp-toggle">
                <input type="checkbox" name="trainer_startseite" id="trainer_startseite" value="1"
                       <?php checked($startseite, '1'); ?>>
                <label for="trainer_startseite">Auf der Startseite anzeigen</label>
            </div>
            <p class="desc" style="margin-top:6px;">Nur markierte Personen erscheinen im Team-Abschnitt auf der Startseite (max. 6 werden angezeigt).</p>
        </div>

        <hr class="gfp-sep">

        <div class="gfp-field">
            <label for="trainer_role">Rolle / Titel</label>
            <p class="desc">z. B. „Leadership Coach" oder „Organisationsberaterin"</p>
            <input type="text" name="trainer_role" id="trainer_role"
                   value="<?php echo esc_attr($role); ?>"
                   placeholder="z. B. Leadership Coach">
        </div>
        <div class="gfp-field">
            <label for="trainer_bio">Kurzbeschreibung (1–2 Sätze)</label>
            <textarea name="trainer_bio" id="trainer_bio"
                      placeholder="z. B. Maria begleitet Führungskräfte seit 15 Jahren dabei, authentisch und wirksam zu führen."><?php echo esc_textarea($bio); ?></textarea>
        </div>
        <div class="gfp-field">
            <label for="trainer_tags">Schwerpunkte / Tags</label>
            <p class="desc">Kommagetrennt, z. B. „Führung, Coaching, Teams"</p>
            <input type="text" name="trainer_tags" id="trainer_tags"
                   value="<?php echo esc_attr($tags); ?>"
                   placeholder="Führung, Coaching, Teams">
        </div>
    </div>
    <?php
}

add_action('save_post_trainer', function (int $post_id): void {
    if (!isset($_POST['gfp_trainer_nonce'])) return;
    if (!wp_verify_nonce($_POST['gfp_trainer_nonce'], 'gfp_trainer_save')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    update_post_meta($post_id, '_trainer_role',
        sanitize_text_field(wp_unslash($_POST['trainer_role'] ?? '')));
    update_post_meta($post_id, '_trainer_bio',
        sanitize_textarea_field(wp_unslash($_POST['trainer_bio'] ?? '')));
    update_post_meta($post_id, '_trainer_tags',
        sanitize_text_field(wp_unslash($_POST['trainer_tags'] ?? '')));

    $gruppe = sanitize_text_field(wp_unslash($_POST['trainer_gruppe'] ?? 'trainerin'));
    if (!in_array($gruppe, ['team', 'trainerin', 'netzwerk'], true)) $gruppe = 'trainerin';
    update_post_meta($post_id, '_trainer_gruppe', $gruppe);

    update_post_meta($post_id, '_trainer_startseite',
        isset($_POST['trainer_startseite']) ? '1' : '0');
});
