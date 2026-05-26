<?php
defined('ABSPATH') || exit;

// ─── Über uns: Meta Box ───────────────────────────────────────────────────────

add_action('add_meta_boxes_page', function (WP_Post $post): void {
    if (get_page_template_slug($post->ID) !== 'page-ueber-uns.php') return;
    add_meta_box(
        'gfp_uu_meta',
        'Über uns – Seiteninhalte',
        'gfp_render_uu_meta_box',
        'page', 'normal', 'high'
    );
});

function gfp_render_uu_meta_box(WP_Post $post): void {
    wp_nonce_field('gfp_uu_save', 'gfp_uu_nonce');
    $f = fn(string $key, string $default = '') => esc_attr(get_post_meta($post->ID, '_uu_' . $key, true) ?: $default);
    $t = fn(string $key) => esc_textarea(get_post_meta($post->ID, '_uu_' . $key, true) ?: '');

    $partners_raw = get_post_meta($post->ID, '_uu_partners', true) ?: '[]';
    $partners     = json_decode($partners_raw, true) ?: [];
    ?>
    <style>
        .gfp-uu-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-top: 4px; }
        .gfp-uu-field { display: flex; flex-direction: column; gap: 4px; }
        .gfp-uu-field.full { grid-column: 1 / -1; }
        .gfp-uu-field label { font-weight: 600; font-size: 12px; text-transform: uppercase; letter-spacing: .05em; color: #555; }
        .gfp-uu-field input,
        .gfp-uu-field textarea { width: 100%; padding: 7px 10px; border: 1px solid #ddd; border-radius: 3px; font-size: 13px; box-sizing: border-box; }
        .gfp-uu-field textarea { resize: vertical; min-height: 100px; }
        .gfp-uu-sep   { grid-column: 1 / -1; border: none; border-top: 2px solid #eee; margin: 8px 0 4px; }
        .gfp-uu-stitle { grid-column: 1 / -1; font-size: 12px; font-weight: 700; text-transform: uppercase;
                         letter-spacing: .08em; color: #0073bc; margin: 0; }
        .gfp-uu-hint   { grid-column: 1 / -1; background: #f0f7ff; border: 1px solid #b8d4f0;
                         border-radius: 3px; padding: 8px 12px; font-size: 12px; color: #444; margin: 0; }
        /* Partner-Repeater */
        #uu-partner-list { display: flex; flex-direction: column; gap: 8px; margin-bottom: 8px; }
        .uu-pr { display: flex; gap: 8px; align-items: center; }
        .uu-pr input { flex: 1; padding: 6px 8px; border: 1px solid #ddd; border-radius: 3px; font-size: 13px; }
        .uu-pr .uu-rm { background: none; border: 1px solid #ccc; border-radius: 3px;
                        cursor: pointer; padding: 4px 8px; color: #888; font-size: 12px; white-space: nowrap; }
        .uu-pr .uu-rm:hover { border-color: #c00; color: #c00; }
        #uu-add-partner { background: #f0f7ff; border: 1px dashed #0073bc; color: #0073bc;
                          padding: 6px 14px; border-radius: 3px; cursor: pointer; font-size: 13px;
                          margin-top: 4px; }
    </style>

    <div class="gfp-uu-grid">

        <!-- HERO -->
        <p class="gfp-uu-stitle">Hero</p>

        <div class="gfp-uu-field">
            <label for="uu_hero_badge">Badge (blaue Pille oben links)</label>
            <input type="text" id="uu_hero_badge" name="uu_hero_badge"
                   value="<?php echo $f('hero_badge', 'GfP'); ?>">
        </div>
        <div class="gfp-uu-field">
            <label for="uu_hero_title">Seiten-Titel (H1)</label>
            <input type="text" id="uu_hero_title" name="uu_hero_title"
                   value="<?php echo $f('hero_title', 'Über uns'); ?>">
        </div>

        <hr class="gfp-uu-sep">

        <!-- EINLEITUNGSTEXT -->
        <p class="gfp-uu-stitle">Einleitungstext</p>

        <div class="gfp-uu-field full">
            <label for="uu_unternehmen_text">Text (Absätze mit Leerzeile trennen — erscheint direkt unter dem Titel)</label>
            <textarea id="uu_unternehmen_text" name="uu_unternehmen_text"
                      style="min-height:180px;"><?php echo $t('unternehmen_text'); ?></textarea>
        </div>

        <hr class="gfp-uu-sep">

        <!-- FIRMENMITGLIEDER -->
        <p class="gfp-uu-stitle">Firmenmitglieder</p>
        <p class="gfp-uu-hint">💡 Personen mit Gruppe „Firmenmitglied" (im Trainer-Menü gepflegt). Erscheinen als größere Karten.</p>

        <div class="gfp-uu-field full">
            <label for="uu_firm_heading">Abschnitts-Überschrift</label>
            <input type="text" id="uu_firm_heading" name="uu_firm_heading"
                   value="<?php echo $f('firm_heading', 'Unser Team'); ?>">
        </div>
        <div class="gfp-uu-field full">
            <label for="uu_firm_sub">Subtext (optional)</label>
            <input type="text" id="uu_firm_sub" name="uu_firm_sub"
                   value="<?php echo $f('firm_sub', ''); ?>">
        </div>

        <hr class="gfp-uu-sep">

        <!-- TRAINERINNEN -->
        <p class="gfp-uu-stitle">Beraterinnen &amp; Trainerinnen</p>
        <p class="gfp-uu-hint">💡 Personen mit Gruppe „Trainerin / Trainer" (im Trainer-Menü gepflegt).</p>

        <div class="gfp-uu-field full">
            <label for="uu_trainers_heading">Abschnitts-Überschrift</label>
            <input type="text" id="uu_trainers_heading" name="uu_trainers_heading"
                   value="<?php echo $f('trainers_heading', 'Beraterinnen & Trainerinnen'); ?>">
        </div>
        <div class="gfp-uu-field full">
            <label for="uu_trainers_sub">Subtext (optional)</label>
            <input type="text" id="uu_trainers_sub" name="uu_trainers_sub"
                   value="<?php echo $f('trainers_sub', ''); ?>">
        </div>

        <hr class="gfp-uu-sep">

        <!-- NETZWERKPARTNERINNEN -->
        <p class="gfp-uu-stitle">Netzwerkpartnerinnen &amp; -partner</p>
        <p class="gfp-uu-hint">💡 Personen mit Gruppe „Netzwerkpartnerin / -partner" (im Trainer-Menü gepflegt). Erscheinen als mittlere Karten.</p>

        <div class="gfp-uu-field full">
            <label for="uu_network_heading">Abschnitts-Überschrift</label>
            <input type="text" id="uu_network_heading" name="uu_network_heading"
                   value="<?php echo $f('network_heading', 'Netzwerkpartnerinnen & -partner'); ?>">
        </div>
        <div class="gfp-uu-field full">
            <label for="uu_network_sub">Subtext (optional)</label>
            <input type="text" id="uu_network_sub" name="uu_network_sub"
                   value="<?php echo $f('network_sub', ''); ?>">
        </div>

        <hr class="gfp-uu-sep">

        <!-- PARTNER -->
        <p class="gfp-uu-stitle">Partner</p>

        <div class="gfp-uu-field full">
            <label for="uu_partner_heading">Abschnitts-Überschrift</label>
            <input type="text" id="uu_partner_heading" name="uu_partner_heading"
                   value="<?php echo $f('partner_heading', 'Unsere Partner'); ?>">
        </div>
        <div class="gfp-uu-field full">
            <label for="uu_partner_text">Beschreibungstext (optional)</label>
            <textarea id="uu_partner_text" name="uu_partner_text"
                      style="min-height:60px;"><?php echo $t('partner_text'); ?></textarea>
        </div>
        <div class="gfp-uu-field full">
            <label>Partner-Liste (Name + Website-URL)</label>
            <div id="uu-partner-list">
                <?php foreach ($partners as $p) : ?>
                <div class="uu-pr">
                    <input type="text"  placeholder="Name"       data-field="name" value="<?php echo esc_attr($p['name'] ?? ''); ?>">
                    <input type="text"  placeholder="https://…"  data-field="url"  value="<?php echo esc_attr($p['url']  ?? ''); ?>">
                    <button type="button" class="uu-rm">✕ Entfernen</button>
                </div>
                <?php endforeach; ?>
            </div>
            <button type="button" id="uu-add-partner">+ Partner hinzufügen</button>
            <input type="hidden" name="uu_partners" id="uu-partners-json"
                   value="<?php echo esc_attr($partners_raw); ?>">
        </div>

    </div>

    <script>
    (function () {
        const list = document.getElementById('uu-partner-list');
        const json = document.getElementById('uu-partners-json');

        function serialize() {
            const data = [];
            list.querySelectorAll('.uu-pr').forEach(function (row) {
                const name = row.querySelector('[data-field="name"]').value.trim();
                const url  = row.querySelector('[data-field="url"]').value.trim();
                if (name) data.push({ name: name, url: url });
            });
            json.value = JSON.stringify(data);
        }

        function bindRow(row) {
            row.querySelector('.uu-rm').addEventListener('click', function () {
                row.remove(); serialize();
            });
            row.querySelectorAll('input').forEach(function (i) {
                i.addEventListener('input', serialize);
            });
        }

        list.querySelectorAll('.uu-pr').forEach(bindRow);

        document.getElementById('uu-add-partner').addEventListener('click', function () {
            const row = document.createElement('div');
            row.className = 'uu-pr';
            row.innerHTML = '<input type="text" placeholder="Name" data-field="name">'
                          + '<input type="text" placeholder="https://…" data-field="url">'
                          + '<button type="button" class="uu-rm">✕ Entfernen</button>';
            list.appendChild(row);
            bindRow(row);
            row.querySelector('input').focus();
        });
    })();
    </script>
    <?php
}

// ─── Speichern ────────────────────────────────────────────────────────────────

add_action('save_post_page', function (int $post_id): void {
    if (!isset($_POST['gfp_uu_nonce'])) return;
    if (!wp_verify_nonce($_POST['gfp_uu_nonce'], 'gfp_uu_save')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;
    if (get_page_template_slug($post_id) !== 'page-ueber-uns.php') return;

    $text_fields = [
        'uu_hero_badge', 'uu_hero_title',
        'uu_firm_heading',    'uu_firm_sub',
        'uu_trainers_heading', 'uu_trainers_sub',
        'uu_network_heading', 'uu_network_sub',
        'uu_partner_heading',
    ];
    foreach ($text_fields as $field) {
        update_post_meta($post_id, '_' . $field,
            sanitize_text_field(wp_unslash($_POST[$field] ?? '')));
    }
    foreach (['uu_unternehmen_text', 'uu_partner_text'] as $field) {
        update_post_meta($post_id, '_' . $field,
            sanitize_textarea_field(wp_unslash($_POST[$field] ?? '')));
    }

    $raw = wp_unslash($_POST['uu_partners'] ?? '[]');
    $arr = json_decode($raw, true);
    if (!is_array($arr)) $arr = [];
    $clean = [];
    foreach ($arr as $p) {
        $name = sanitize_text_field($p['name'] ?? '');
        $url  = esc_url_raw($p['url'] ?? '');
        if ($name) $clean[] = ['name' => $name, 'url' => $url];
    }
    update_post_meta($post_id, '_uu_partners', wp_json_encode($clean));
});
