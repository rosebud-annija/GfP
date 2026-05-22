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
    $role = get_post_meta($post->ID, '_trainer_role', true) ?: '';
    $bio  = get_post_meta($post->ID, '_trainer_bio',  true) ?: '';
    $tags = get_post_meta($post->ID, '_trainer_tags', true) ?: '';
    ?>
    <style>
    .gfp-tr .gfp-field { margin-bottom:18px; }
    .gfp-tr .gfp-field label { display:block; font-weight:600; font-size:13px; margin-bottom:5px; color:#333; }
    .gfp-tr .gfp-field .desc { font-size:12px; color:#888; margin-bottom:5px; }
    .gfp-tr .gfp-field input[type=text],
    .gfp-tr .gfp-field textarea { width:100%; padding:8px 10px; border:1px solid #ccc; border-radius:4px; font-size:14px; box-sizing:border-box; }
    .gfp-tr .gfp-field textarea { min-height:90px; resize:vertical; }
    .gfp-tr .gfp-hint { background:#f0f7ff; border:1px solid #b8d4f0; border-radius:4px; padding:10px 14px; font-size:13px; color:#444; margin-bottom:20px; }
    </style>
    <div class="gfp-tr">
        <p class="gfp-hint">📷 <strong>Foto:</strong> Bitte das Trainerfoto als <em>Beitragsbild</em> in der rechten Seitenleiste hochladen. Empfohlen: quadratisch, mind. 400×400 px.</p>
        <div class="gfp-field">
            <label for="trainer_role">Rolle / Titel</label>
            <p class="desc">z. B. „Leadership Coach" oder „Organisationsberaterin"</p>
            <input type="text" name="trainer_role" id="trainer_role"
                   value="<?php echo esc_attr($role); ?>"
                   placeholder="z. B. Leadership Coach">
        </div>
        <div class="gfp-field">
            <label for="trainer_bio">Kurzbiografie (1–2 Sätze)</label>
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
});
