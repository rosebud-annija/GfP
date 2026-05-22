<?php
defined('ABSPATH') || exit;

// ─── Kurs Meta Box ────────────────────────────────────────────────────────────

add_action('add_meta_boxes', function () {
    add_meta_box(
        'gfp_kurs_details',
        'Kurs-Details',
        'gfp_render_kurs_meta_box',
        'kurs',
        'normal',
        'high'
    );
});

function gfp_render_kurs_meta_box(WP_Post $post): void {
    wp_nonce_field('gfp_kurs_save', 'gfp_kurs_nonce');

    $format    = get_post_meta($post->ID, '_kurs_format',    true) ?: '';
    $lernziele = json_decode(get_post_meta($post->ID, '_kurs_lernziele', true) ?: '[]', true) ?: [];
    $trainer   = json_decode(get_post_meta($post->ID, '_kurs_trainer',   true) ?: '[]', true) ?: [];
    $termine   = json_decode(get_post_meta($post->ID, '_kurs_termine',   true) ?: '[]', true) ?: [];
    $cta_label = get_post_meta($post->ID, '_kurs_cta_label', true) ?: 'Jetzt anmelden';
    $cta_url   = get_post_meta($post->ID, '_kurs_cta_url',   true) ?: '';

    $format_opts = [
        'academy'    => '🎓 Academy — offenes Seminar',
        'inhouse'    => '🏢 Inhouse — firmeninternes Training',
        'coaching'   => '👤 Coaching (1:1 oder Team)',
        'consulting' => '💼 Consulting',
    ];
    ?>
    <style>
    .gfp-tabs { display:flex; gap:4px; border-bottom:2px solid #ddd; margin-bottom:20px; flex-wrap:wrap; }
    .gfp-tab  { padding:8px 16px; cursor:pointer; border:1px solid #ddd; border-bottom:none; background:#f9f9f9; font-size:13px; font-weight:600; border-radius:4px 4px 0 0; color:#555; }
    .gfp-tab.active { background:#fff; border-bottom:2px solid #fff; margin-bottom:-2px; color:#111; }
    .gfp-panel { display:none; } .gfp-panel.active { display:block; }

    .gfp-field { margin-bottom:16px; }
    .gfp-field label { display:block; font-weight:600; font-size:13px; margin-bottom:5px; color:#333; }
    .gfp-field .desc  { font-size:12px; color:#666; margin-bottom:6px; }
    .gfp-field input[type=text],
    .gfp-field input[type=url],
    .gfp-field input[type=date],
    .gfp-field select,
    .gfp-field textarea { width:100%; padding:8px 10px; border:1px solid #ccc; border-radius:4px; font-size:14px; }
    .gfp-field textarea { min-height:70px; resize:vertical; }

    .gfp-repeater { border:1px solid #e0e0e0; border-radius:6px; overflow:hidden; margin-bottom:12px; }
    .gfp-repeater-head { background:#f0f0f0; padding:8px 14px; font-size:12px; font-weight:700; color:#555; text-transform:uppercase; letter-spacing:.06em; display:grid; gap:10px; }
    .gfp-repeater-row  { padding:14px; border-top:1px solid #e8e8e8; display:grid; gap:10px; background:#fff; position:relative; }
    .gfp-repeater-row:nth-child(even) { background:#fafafa; }
    .gfp-repeater-row input,
    .gfp-repeater-row select,
    .gfp-repeater-row textarea { width:100%; padding:7px 9px; border:1px solid #ccc; border-radius:4px; font-size:13px; box-sizing:border-box; }
    .gfp-repeater-row textarea { min-height:60px; resize:vertical; }
    .gfp-remove { position:absolute; top:10px; right:12px; background:none; border:none; color:#c00; font-size:18px; cursor:pointer; line-height:1; padding:2px 6px; border-radius:3px; }
    .gfp-remove:hover { background:#fef0f0; }
    .gfp-add { margin-top:10px; background:#2271b1; color:#fff; border:none; padding:8px 16px; border-radius:4px; cursor:pointer; font-size:13px; font-weight:600; }
    .gfp-add:hover { background:#135e96; }

    .gfp-cols-2 { grid-template-columns:1fr 1fr; }
    .gfp-cols-4 { grid-template-columns:1fr 1fr 1fr 100px; }
    .gfp-cols-trainer { grid-template-columns:1fr 1fr; }

    .gfp-toggle-label { display:flex; align-items:center; gap:8px; font-size:13px; }
    .gfp-toggle-label input { width:auto; }
    </style>

    <div class="gfp-tabs">
        <div class="gfp-tab active" data-panel="basisinfos">📋 Basisinfos</div>
        <div class="gfp-tab" data-panel="lernziele">🎯 Lernziele</div>
        <div class="gfp-tab" data-panel="trainer">👥 Trainer & Trainerinnen</div>
        <div class="gfp-tab" data-panel="termine">📅 Termine</div>
        <div class="gfp-tab" data-panel="buchung">🔗 Buchung</div>
    </div>

    <!-- ── Basisinfos ──────────────────────────────────────────────────── -->
    <div class="gfp-panel active" id="panel-basisinfos">
        <p class="desc" style="margin-bottom:16px;color:#666;font-size:13px;">
            💡 Die <strong>Kurzbeschreibung</strong> (für Karte &amp; Meta) bitte im Feld
            <em>„Auszug"</em> in der rechten Seitenleiste eingeben.
        </p>
        <div class="gfp-field">
            <label for="kurs_format">Format *</label>
            <select name="kurs_format" id="kurs_format" required>
                <option value="">— bitte wählen —</option>
                <?php foreach ($format_opts as $val => $label) : ?>
                    <option value="<?php echo esc_attr($val); ?>" <?php selected($format, $val); ?>>
                        <?php echo esc_html($label); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
            <div class="gfp-field">
                <label for="kurs_teilnehmer">Teilnehmerzahl</label>
                <p class="desc">Wird als Infokästchen rechts angezeigt.</p>
                <input type="text" name="kurs_teilnehmer" id="kurs_teilnehmer"
                       value="<?php echo esc_attr(get_post_meta($post->ID, '_kurs_teilnehmer', true)); ?>"
                       placeholder="z. B. max. 12 Personen">
            </div>
            <div class="gfp-field">
                <label for="kurs_investition">Investition / Preis</label>
                <p class="desc">Wird als Infokästchen rechts angezeigt.</p>
                <input type="text" name="kurs_investition" id="kurs_investition"
                       value="<?php echo esc_attr(get_post_meta($post->ID, '_kurs_investition', true)); ?>"
                       placeholder="z. B. EUR 2.900,– zzgl. MwSt.">
            </div>
        </div>
        <div class="gfp-field">
            <label for="kurs_voraussetzungen">Voraussetzungen</label>
            <p class="desc">Kurzer Hinweis was Teilnehmende mitbringen sollten.</p>
            <textarea name="kurs_voraussetzungen" id="kurs_voraussetzungen"
                      placeholder="z. B. Keine besonderen Vorkenntnisse erforderlich."><?php echo esc_textarea(get_post_meta($post->ID, '_kurs_voraussetzungen', true)); ?></textarea>
        </div>
        <div class="gfp-field">
            <label for="kurs_infoabend">Infoveranstaltung / Nächster Infoabend</label>
            <p class="desc">Optional: Datum + Ort oder Link zum Infoabend.</p>
            <input type="text" name="kurs_infoabend" id="kurs_infoabend"
                   value="<?php echo esc_attr(get_post_meta($post->ID, '_kurs_infoabend', true)); ?>"
                   placeholder="z. B. 15. Juni 2025, online – kostenlos">
        </div>
    </div>

    <!-- ── Lernziele ──────────────────────────────────────────────────── -->
    <div class="gfp-panel" id="panel-lernziele">
        <p style="margin-bottom:12px;color:#666;font-size:13px;">
            Was nehmen die Teilnehmenden mit? Beginne mit „Du kannst…" oder „Du verstehst…"
        </p>
        <div class="gfp-repeater" id="lernziele-list">
            <?php foreach ($lernziele as $i => $lz) : ?>
            <div class="gfp-repeater-row">
                <input type="text" name="kurs_lernziele[]"
                       value="<?php echo esc_attr($lz); ?>"
                       placeholder="z. B. Du kannst Feedback strukturiert geben.">
                <button type="button" class="gfp-remove" title="Entfernen">×</button>
            </div>
            <?php endforeach; ?>
        </div>
        <button type="button" class="gfp-add" data-target="lernziele-list" data-template="lernziel-tpl">
            + Lernziel hinzufügen
        </button>
        <template id="lernziel-tpl">
            <div class="gfp-repeater-row">
                <input type="text" name="kurs_lernziele[]"
                       placeholder="z. B. Du kannst Feedback strukturiert geben.">
                <button type="button" class="gfp-remove" title="Entfernen">×</button>
            </div>
        </template>
    </div>

    <!-- ── Trainer & Trainerinnen ─────────────────────────────────────── -->
    <div class="gfp-panel" id="panel-trainer">
        <p style="margin-bottom:12px;color:#666;font-size:13px;">
            Wer leitet diesen Kurs? Mehrere Trainer möglich.
        </p>
        <div class="gfp-repeater" id="trainer-list">
            <?php foreach ($trainer as $i => $t) : ?>
            <div class="gfp-repeater-row gfp-cols-trainer" style="grid-template-columns:1fr 1fr;gap:10px;">
                <div>
                    <label style="font-size:12px;font-weight:600;color:#555;">Name *</label>
                    <input type="text" name="kurs_trainer[<?php echo $i; ?>][name]"
                           value="<?php echo esc_attr($t['name'] ?? ''); ?>"
                           placeholder="z. B. Maria Berger">
                </div>
                <div>
                    <label style="font-size:12px;font-weight:600;color:#555;">Rolle / Titel</label>
                    <input type="text" name="kurs_trainer[<?php echo $i; ?>][rolle]"
                           value="<?php echo esc_attr($t['rolle'] ?? ''); ?>"
                           placeholder="z. B. Leadership Coach">
                </div>
                <div style="grid-column:1/-1;">
                    <label style="font-size:12px;font-weight:600;color:#555;">Kurzbiografie (1–2 Sätze)</label>
                    <textarea name="kurs_trainer[<?php echo $i; ?>][bio]"
                              placeholder="z. B. Maria begleitet Führungskräfte seit 15 Jahren…"><?php echo esc_textarea($t['bio'] ?? ''); ?></textarea>
                </div>
                <div style="grid-column:1/-1;">
                    <label style="font-size:12px;font-weight:600;color:#555;">Foto-URL (aus Mediathek)</label>
                    <div style="display:flex;gap:8px;">
                        <input type="text" name="kurs_trainer[<?php echo $i; ?>][foto_url]"
                               value="<?php echo esc_attr($t['foto_url'] ?? ''); ?>"
                               placeholder="https://…/foto.jpg" style="flex:1;">
                        <button type="button" class="button gfp-media-btn"
                                data-target="kurs_trainer[<?php echo $i; ?>][foto_url]">
                            📷 Wählen
                        </button>
                    </div>
                </div>
                <button type="button" class="gfp-remove" title="Entfernen">×</button>
            </div>
            <?php endforeach; ?>
        </div>
        <button type="button" class="gfp-add" data-target="trainer-list" data-template="trainer-tpl">
            + Trainer / Trainerin hinzufügen
        </button>
        <template id="trainer-tpl">
            <div class="gfp-repeater-row gfp-cols-trainer" style="grid-template-columns:1fr 1fr;gap:10px;" data-index="__i__">
                <div>
                    <label style="font-size:12px;font-weight:600;color:#555;">Name *</label>
                    <input type="text" name="kurs_trainer[__i__][name]" placeholder="z. B. Maria Berger">
                </div>
                <div>
                    <label style="font-size:12px;font-weight:600;color:#555;">Rolle / Titel</label>
                    <input type="text" name="kurs_trainer[__i__][rolle]" placeholder="z. B. Leadership Coach">
                </div>
                <div style="grid-column:1/-1;">
                    <label style="font-size:12px;font-weight:600;color:#555;">Kurzbiografie (1–2 Sätze)</label>
                    <textarea name="kurs_trainer[__i__][bio]" placeholder="z. B. Maria begleitet Führungskräfte seit 15 Jahren…"></textarea>
                </div>
                <div style="grid-column:1/-1;">
                    <label style="font-size:12px;font-weight:600;color:#555;">Foto-URL (aus Mediathek)</label>
                    <div style="display:flex;gap:8px;">
                        <input type="text" name="kurs_trainer[__i__][foto_url]" placeholder="https://…/foto.jpg" style="flex:1;">
                        <button type="button" class="button gfp-media-btn" data-target="kurs_trainer[__i__][foto_url]">📷 Wählen</button>
                    </div>
                </div>
                <button type="button" class="gfp-remove" title="Entfernen">×</button>
            </div>
        </template>
    </div>

    <!-- ── Termine ────────────────────────────────────────────────────── -->
    <div class="gfp-panel" id="panel-termine">
        <p style="margin-bottom:12px;color:#666;font-size:13px;">
            Alle Termine eintragen. Vergangene können drin bleiben — die Website zeigt nur zukünftige.
        </p>
        <div class="gfp-field">
            <label for="kurs_termine_notiz">Allgemeiner Terminhinweis (optional)</label>
            <p class="desc">Erscheint im Infokästchen „Termine / Ort" auf der Kursseite.</p>
            <textarea name="kurs_termine_notiz" id="kurs_termine_notiz"
                      rows="3"
                      placeholder="z. B. Start jederzeit möglich, Termine und Veranstaltungsorte werden mit den TeilnehmerInnen vereinbart."><?php echo esc_textarea(get_post_meta($post->ID, '_kurs_termine_notiz', true)); ?></textarea>
        </div>
        <div class="gfp-repeater" id="termine-list">
            <div class="gfp-repeater-head gfp-cols-4" style="grid-template-columns:1fr 1fr 1fr 100px;">
                <span>Start-Datum *</span><span>End-Datum</span><span>Ort</span><span>Ausgebucht</span>
            </div>
            <?php foreach ($termine as $i => $t) : ?>
            <div class="gfp-repeater-row gfp-cols-4" style="grid-template-columns:1fr 1fr 1fr 100px;align-items:start;">
                <input type="date" name="kurs_termine[<?php echo $i; ?>][datum]"
                       value="<?php echo esc_attr($t['datum'] ?? ''); ?>">
                <input type="date" name="kurs_termine[<?php echo $i; ?>][datum_ende]"
                       value="<?php echo esc_attr($t['datum_ende'] ?? ''); ?>">
                <input type="text" name="kurs_termine[<?php echo $i; ?>][ort]"
                       value="<?php echo esc_attr($t['ort'] ?? ''); ?>"
                       placeholder="z. B. München">
                <label class="gfp-toggle-label">
                    <input type="checkbox" name="kurs_termine[<?php echo $i; ?>][ausgebucht]"
                           value="1" <?php checked(!empty($t['ausgebucht'])); ?>>
                    Ausgebucht
                </label>
                <button type="button" class="gfp-remove" title="Entfernen">×</button>
            </div>
            <?php endforeach; ?>
        </div>
        <button type="button" class="gfp-add" data-target="termine-list" data-template="termin-tpl">
            + Termin hinzufügen
        </button>
        <template id="termin-tpl">
            <div class="gfp-repeater-row gfp-cols-4" style="grid-template-columns:1fr 1fr 1fr 100px;align-items:start;">
                <input type="date" name="kurs_termine[__i__][datum]">
                <input type="date" name="kurs_termine[__i__][datum_ende]">
                <input type="text" name="kurs_termine[__i__][ort]" placeholder="z. B. München">
                <label class="gfp-toggle-label">
                    <input type="checkbox" name="kurs_termine[__i__][ausgebucht]" value="1">
                    Ausgebucht
                </label>
                <button type="button" class="gfp-remove" title="Entfernen">×</button>
            </div>
        </template>
    </div>

    <!-- ── Buchung ────────────────────────────────────────────────────── -->
    <div class="gfp-panel" id="panel-buchung">
        <div class="gfp-field">
            <label for="kurs_cta_label">Button-Text</label>
            <p class="desc">Leer lassen → „Jetzt anmelden"</p>
            <input type="text" name="kurs_cta_label" id="kurs_cta_label"
                   value="<?php echo esc_attr($cta_label); ?>"
                   placeholder="Jetzt anmelden">
        </div>
        <div class="gfp-field">
            <label for="kurs_cta_url">Buchungs-URL</label>
            <p class="desc">Link zum Buchungssystem. Leer lassen → E-Mail-Fallback.</p>
            <input type="url" name="kurs_cta_url" id="kurs_cta_url"
                   value="<?php echo esc_attr($cta_url); ?>"
                   placeholder="https://buchung.beispiel.de/kurs-id">
        </div>
    </div>

    <script>
    (function() {
        // ── Tabs ──────────────────────────────────────────────────────────
        document.querySelectorAll('.gfp-tab').forEach(function(tab) {
            tab.addEventListener('click', function() {
                document.querySelectorAll('.gfp-tab').forEach(t => t.classList.remove('active'));
                document.querySelectorAll('.gfp-panel').forEach(p => p.classList.remove('active'));
                tab.classList.add('active');
                document.getElementById('panel-' + tab.dataset.panel).classList.add('active');
            });
        });

        // ── Repeater: Zeile hinzufügen ────────────────────────────────────
        var counters = { lernziele: <?php echo max(count($lernziele), 0); ?>, trainer: <?php echo max(count($trainer), 0); ?>, termine: <?php echo max(count($termine), 0); ?> };

        document.querySelectorAll('.gfp-add').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var listId  = btn.dataset.target;
                var tplId   = btn.dataset.template;
                var list    = document.getElementById(listId);
                var tpl     = document.getElementById(tplId);
                if (!list || !tpl) return;

                var key = listId.replace('-list', '');
                if (!counters[key]) counters[key] = 0;
                var idx = counters[key]++;

                var html = tpl.innerHTML.replace(/__i__/g, idx);
                var tmp  = document.createElement('div');
                tmp.innerHTML = html;
                var row = tmp.firstElementChild;
                list.appendChild(row);
                attachRemove(row);
                attachMedia(row);
            });
        });

        // ── Repeater: Zeile entfernen ─────────────────────────────────────
        function attachRemove(ctx) {
            ctx.querySelectorAll('.gfp-remove').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    btn.closest('.gfp-repeater-row').remove();
                });
            });
        }
        document.querySelectorAll('.gfp-repeater-row').forEach(attachRemove);

        // ── Mediathek-Button ──────────────────────────────────────────────
        function attachMedia(ctx) {
            (ctx ? ctx.querySelectorAll('.gfp-media-btn') : document.querySelectorAll('.gfp-media-btn'))
            .forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var targetName = btn.dataset.target;
                    var frame = wp.media({ title: 'Foto wählen', button: { text: 'Verwenden' }, multiple: false });
                    frame.on('select', function() {
                        var att = frame.state().get('selection').first().toJSON();
                        var input = document.querySelector('input[name="' + targetName + '"]');
                        if (input) input.value = att.url;
                    });
                    frame.open();
                });
            });
        }
        attachMedia(document);
    })();
    </script>
    <?php
}

// ─── Kurs speichern ───────────────────────────────────────────────────────────

add_action('save_post_kurs', function (int $post_id): void {
    if (!isset($_POST['gfp_kurs_nonce'])) return;
    if (!wp_verify_nonce($_POST['gfp_kurs_nonce'], 'gfp_kurs_save')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    $erlaubte_formate = ['academy', 'inhouse', 'coaching', 'consulting'];
    $format = sanitize_text_field($_POST['kurs_format'] ?? '');
    if (in_array($format, $erlaubte_formate, true)) {
        update_post_meta($post_id, '_kurs_format', $format);
    }

    $lernziele_raw = wp_unslash($_POST['kurs_lernziele'] ?? []);
    $lernziele = array_values(array_filter(array_map('sanitize_text_field', (array) $lernziele_raw)));
    update_post_meta($post_id, '_kurs_lernziele', wp_json_encode($lernziele, JSON_UNESCAPED_UNICODE));

    $trainer_raw = wp_unslash($_POST['kurs_trainer'] ?? []);
    $trainer = [];
    foreach ((array) $trainer_raw as $t) {
        $name = sanitize_text_field($t['name'] ?? '');
        if (!$name) continue;
        $trainer[] = [
            'name'     => $name,
            'rolle'    => sanitize_text_field($t['rolle']    ?? ''),
            'bio'      => sanitize_textarea_field($t['bio']  ?? ''),
            'foto_url' => esc_url_raw($t['foto_url']         ?? ''),
        ];
    }
    update_post_meta($post_id, '_kurs_trainer', wp_json_encode($trainer, JSON_UNESCAPED_UNICODE));

    $termine_raw = wp_unslash($_POST['kurs_termine'] ?? []);
    $termine = [];
    foreach ((array) $termine_raw as $t) {
        $datum = sanitize_text_field($t['datum'] ?? '');
        if (!$datum) continue;
        $termine[] = [
            'datum'      => $datum,
            'datum_ende' => sanitize_text_field($t['datum_ende'] ?? ''),
            'ort'        => sanitize_text_field($t['ort']        ?? ''),
            'ausgebucht' => !empty($t['ausgebucht']),
        ];
    }
    usort($termine, fn($a, $b) => strcmp($a['datum'], $b['datum']));
    update_post_meta($post_id, '_kurs_termine', wp_json_encode($termine, JSON_UNESCAPED_UNICODE));

    update_post_meta($post_id, '_kurs_termine_notiz',   sanitize_textarea_field(wp_unslash($_POST['kurs_termine_notiz']   ?? '')));
    update_post_meta($post_id, '_kurs_cta_label',       sanitize_text_field($_POST['kurs_cta_label']                      ?? ''));
    update_post_meta($post_id, '_kurs_cta_url',         esc_url_raw($_POST['kurs_cta_url']                                ?? ''));
    update_post_meta($post_id, '_kurs_teilnehmer',      sanitize_text_field($_POST['kurs_teilnehmer']                     ?? ''));
    update_post_meta($post_id, '_kurs_investition',     sanitize_text_field($_POST['kurs_investition']                    ?? ''));
    update_post_meta($post_id, '_kurs_voraussetzungen', sanitize_textarea_field($_POST['kurs_voraussetzungen']            ?? ''));
    update_post_meta($post_id, '_kurs_infoabend',       sanitize_text_field($_POST['kurs_infoabend']                      ?? ''));
});
