<?php
/**
 * GfP Theme — functions.php
 *
 * - Theme-Setup (Fonts, Menus, Features)
 * - Custom Post Type: kurs
 * - Custom Taxonomy: fachbereich (mit Farb-Meta)
 * - ACF-Felder (programmatisch registriert, kein ACF-UI nötig)
 * - Hilfsfunktionen für Templates
 */

defined('ABSPATH') || exit;

// ─── Theme-Setup ──────────────────────────────────────────────────────────────

add_action('after_setup_theme', function () {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', ['search-form', 'comment-form', 'gallery', 'caption']);

    register_nav_menus([
        'primary' => __('Hauptnavigation', 'gfp'),
        'footer'  => __('Footer', 'gfp'),
    ]);
});

// ─── Block Editor deaktivieren (Theme nutzt Classic Editor + native Meta Boxes) ─

add_filter('use_block_editor_for_post_type', function (bool $enabled, string $type): bool {
    return !in_array($type, ['kurs', 'trainer'], true);
}, 10, 2);

// ─── Fonts & Styles laden ─────────────────────────────────────────────────────

add_action('wp_enqueue_scripts', function () {
    // Google Fonts: League Gothic + Host Grotesk
    wp_enqueue_style(
        'gfp-fonts',
        'https://fonts.googleapis.com/css2?family=League+Gothic&family=Host+Grotesk:wght@400;500;600&display=swap',
        [],
        null
    );

    // Theme-Stylesheet
    wp_enqueue_style('gfp-style', get_stylesheet_uri(), ['gfp-fonts'], '1.0.0');

    // Kurs-Filter JS (nur auf Kurs-Archiv)
    if (is_post_type_archive('kurs') || is_tax('fachbereich')) {
        wp_enqueue_script(
            'gfp-kurs-filter',
            get_template_directory_uri() . '/assets/js/kurs-filter.js',
            [],
            '1.0.0',
            true
        );
    }
});

// ─── Custom Post Type: Kurs ───────────────────────────────────────────────────

add_action('init', function () {
    register_post_type('kurs', [
        'labels' => [
            'name'               => __('Kurse', 'gfp'),
            'singular_name'      => __('Kurs', 'gfp'),
            'add_new'            => __('Neuen Kurs anlegen', 'gfp'),
            'add_new_item'       => __('Neuen Kurs anlegen', 'gfp'),
            'edit_item'          => __('Kurs bearbeiten', 'gfp'),
            'all_items'          => __('Alle Kurse', 'gfp'),
            'search_items'       => __('Kurse durchsuchen', 'gfp'),
            'not_found'          => __('Keine Kurse gefunden', 'gfp'),
        ],
        'public'             => true,
        'has_archive'        => true,
        'rewrite'            => ['slug' => 'kurse'],
        'show_in_rest'       => true,
        'supports'           => ['title', 'editor', 'thumbnail', 'excerpt'],
        'menu_icon'          => 'dashicons-welcome-learn-more',
        'menu_position'      => 5,
    ]);
});

// ─── Custom Post Type: Trainer ────────────────────────────────────────────────

add_action('init', function () {
    register_post_type('trainer', [
        'labels' => [
            'name'          => __('Trainer', 'gfp'),
            'singular_name' => __('Trainer·in', 'gfp'),
            'add_new'       => __('Neue·n Trainer·in anlegen', 'gfp'),
            'add_new_item'  => __('Neue·n Trainer·in anlegen', 'gfp'),
            'edit_item'     => __('Trainer·in bearbeiten', 'gfp'),
            'all_items'     => __('Alle Trainer', 'gfp'),
            'not_found'     => __('Keine Trainer gefunden', 'gfp'),
        ],
        'public'         => false,
        'show_ui'        => true,
        'show_in_menu'   => true,
        'supports'       => ['title', 'thumbnail'],
        'menu_icon'      => 'dashicons-groups',
        'menu_position'  => 6,
    ]);
});

// Trainer Meta Box ----------------------------------------------------------

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

// ─── Custom Taxonomy: Fachbereich ─────────────────────────────────────────────

add_action('init', function () {
    register_taxonomy('fachbereich', 'kurs', [
        'labels' => [
            'name'          => __('Fachbereiche', 'gfp'),
            'singular_name' => __('Fachbereich', 'gfp'),
            'all_items'     => __('Alle Fachbereiche', 'gfp'),
            'edit_item'     => __('Fachbereich bearbeiten', 'gfp'),
            'add_new_item'  => __('Neuen Fachbereich anlegen', 'gfp'),
        ],
        'hierarchical'  => true,
        'public'        => true,
        'rewrite'       => ['slug' => 'fachbereich'],
        'show_in_rest'  => true,
        'show_admin_column' => true,
    ]);
});

// Farb-Feld im Fachbereich-Bearbeitungsformular --------------------------------

add_action('fachbereich_edit_form_fields', function ($term) {
    $farbe = get_term_meta($term->term_id, 'farbe', true) ?: 'blue';
    ?>
    <tr class="form-field">
        <th scope="row"><label for="fachbereich-farbe"><?php _e('Farbe', 'gfp'); ?></label></th>
        <td>
            <select name="fachbereich_farbe" id="fachbereich-farbe">
                <?php
                $farben = [
                    'blue'   => 'Leadership & Führung (#0073BC)',
                    'teal'   => 'Facilitation & Moderation (#23B0A5)',
                    'orange' => 'Teams & Collaboration (#F18712)',
                    'purple' => 'Organisation & Transformation (#9E4493)',
                    'sky'    => 'Personality & Skills (#50C1E0)',
                ];
                foreach ($farben as $wert => $label) {
                    printf(
                        '<option value="%s"%s>%s</option>',
                        esc_attr($wert),
                        selected($farbe, $wert, false),
                        esc_html($label)
                    );
                }
                ?>
            </select>
        </td>
    </tr>
    <?php
});

add_action('fachbereich_add_form_fields', function () {
    ?>
    <div class="form-field">
        <label for="fachbereich-farbe"><?php _e('Farbe', 'gfp'); ?></label>
        <select name="fachbereich_farbe" id="fachbereich-farbe">
            <option value="blue">Leadership & Führung (#0073BC)</option>
            <option value="teal">Facilitation & Moderation (#23B0A5)</option>
            <option value="orange">Teams & Collaboration (#F18712)</option>
            <option value="purple">Organisation & Transformation (#9E4493)</option>
            <option value="sky">Personality & Skills (#50C1E0)</option>
        </select>
    </div>
    <?php
});

add_action('edited_fachbereich', 'gfp_save_fachbereich_meta');
add_action('create_fachbereich', 'gfp_save_fachbereich_meta');

function gfp_save_fachbereich_meta($term_id) {
    if (isset($_POST['fachbereich_farbe'])) {
        $erlaubte = ['blue', 'teal', 'orange', 'purple', 'sky'];
        $farbe = sanitize_text_field($_POST['fachbereich_farbe']);
        if (in_array($farbe, $erlaubte, true)) {
            update_term_meta($term_id, 'farbe', $farbe);
        }
    }
}

// ─── ACF: deaktiviert ─────────────────────────────────────────────────────────
// Kurs-Felder werden über native WordPress Meta Boxes verwaltet (siehe unten).
// ACF wird nicht mehr benötigt — das Plugin kann deinstalliert werden.
if (false) { // Nie ausführen
    add_action('acf/init', function () {
    acf_add_local_field_group([
        'key'      => 'group_kurs_felder',
        'title'    => 'Kurs-Details',
        'location' => [[ ['param' => 'post_type', 'operator' => '==', 'value' => 'kurs'] ]],
        'position' => 'normal',
        'style'    => 'seamless',
        'fields'   => [

            // ── Tab 1: Basisinfos ──────────────────────────────────────────
            [
                'key'   => 'field_tab_basis',
                'label' => '📋 Basisinfos',
                'name'  => '',
                'type'  => 'tab',
            ],

            // Kurzbeschreibung (Excerpt wird von WP geliefert — hier als Hinweis)
            [
                'key'         => 'field_kurs_hinweis',
                'label'       => 'Hinweis',
                'name'        => '',
                'type'        => 'message',
                'message'     => '💡 <strong>Kurzbeschreibung</strong> (für Karte & Meta) bitte im Feld "Auszug" (rechte Spalte) eingeben.',
                'new_lines'   => 'wpautop',
                'esc_html'    => 0,
            ],

            // Format
            [
                'key'           => 'field_kurs_format',
                'label'         => 'Format',
                'name'          => 'format',
                'type'          => 'select',
                'instructions'  => 'Wie wird dieser Kurs angeboten?',
                'choices'       => [
                    'academy'    => '🎓 Academy — offenes Seminar',
                    'inhouse'    => '🏢 Inhouse — firmeninternes Training',
                    'coaching'   => '👤 Coaching (1:1 oder Team)',
                    'consulting' => '💼 Consulting',
                ],
                'required'      => 1,
                'allow_null'    => 0,
                'ui'            => 1,
            ],

            // ── Tab 2: Lernziele ───────────────────────────────────────────
            [
                'key'   => 'field_tab_lernziele',
                'label' => '🎯 Lernziele',
                'name'  => '',
                'type'  => 'tab',
            ],

            [
                'key'          => 'field_kurs_lernziele',
                'label'        => 'Lernziele',
                'name'         => 'lernziele',
                'type'         => 'repeater',
                'instructions' => 'Was nehmen die Teilnehmenden mit? Je Lernziel eine Zeile — beginne mit "Du kannst…" oder "Du verstehst…"',
                'button_label' => '+ Lernziel hinzufügen',
                'min'          => 0,
                'layout'       => 'block',
                'sub_fields'   => [
                    [
                        'key'          => 'field_kurs_lernziel_text',
                        'label'        => 'Lernziel',
                        'name'         => 'text',
                        'type'         => 'text',
                        'placeholder'  => 'z. B. Du kannst Feedback strukturiert und wertschätzend geben.',
                        'wrapper'      => ['width' => '100'],
                    ],
                ],
            ],

            // ── Tab 3: Trainer & Trainerinnen ─────────────────────────────
            [
                'key'   => 'field_tab_trainer',
                'label' => '👥 Trainer & Trainerinnen',
                'name'  => '',
                'type'  => 'tab',
            ],

            [
                'key'          => 'field_kurs_trainer',
                'label'        => 'Trainer / Trainerinnen',
                'name'         => 'trainer',
                'type'         => 'repeater',
                'instructions' => 'Wer leitet diesen Kurs? Mehrere Trainer möglich.',
                'button_label' => '+ Trainer / Trainerin hinzufügen',
                'min'          => 0,
                'layout'       => 'block',
                'sub_fields'   => [
                    [
                        'key'          => 'field_trainer_name',
                        'label'        => 'Name',
                        'name'         => 'name',
                        'type'         => 'text',
                        'required'     => 1,
                        'placeholder'  => 'z. B. Maria Berger',
                        'wrapper'      => ['width' => '50'],
                    ],
                    [
                        'key'          => 'field_trainer_rolle',
                        'label'        => 'Rolle / Titel',
                        'name'         => 'rolle',
                        'type'         => 'text',
                        'placeholder'  => 'z. B. Leadership Coach & Organisationsberaterin',
                        'wrapper'      => ['width' => '50'],
                    ],
                    [
                        'key'          => 'field_trainer_bio',
                        'label'        => 'Kurzbiografie (1–2 Sätze)',
                        'name'         => 'bio',
                        'type'         => 'textarea',
                        'rows'         => 3,
                        'placeholder'  => 'z. B. Maria begleitet Führungskräfte seit 15 Jahren dabei, authentisch und wirksam zu führen.',
                        'wrapper'      => ['width' => '100'],
                    ],
                    [
                        'key'           => 'field_trainer_foto',
                        'label'         => 'Foto',
                        'name'          => 'foto',
                        'type'          => 'image',
                        'return_format' => 'array',
                        'preview_size'  => 'medium',
                        'instructions'  => 'Empfohlen: quadratisch, mind. 400×400px.',
                        'wrapper'       => ['width' => '100'],
                    ],
                ],
            ],

            // ── Tab 4: Termine ─────────────────────────────────────────────
            [
                'key'   => 'field_tab_termine',
                'label' => '📅 Termine',
                'name'  => '',
                'type'  => 'tab',
            ],

            [
                'key'          => 'field_kurs_termine',
                'label'        => 'Termine',
                'name'         => 'termine',
                'type'         => 'repeater',
                'instructions' => 'Alle kommenden Termine eintragen. Vergangene können drin bleiben — die Website zeigt nur zukünftige.',
                'button_label' => '+ Termin hinzufügen',
                'min'          => 0,
                'layout'       => 'table',
                'sub_fields'   => [
                    [
                        'key'           => 'field_termin_datum',
                        'label'         => 'Start-Datum',
                        'name'          => 'datum',
                        'type'          => 'date_picker',
                        'display_format'=> 'd.m.Y',
                        'return_format' => 'Y-m-d',
                        'required'      => 1,
                        'wrapper'       => ['width' => '20'],
                    ],
                    [
                        'key'           => 'field_termin_datum_ende',
                        'label'         => 'End-Datum (optional)',
                        'name'          => 'datum_ende',
                        'type'          => 'date_picker',
                        'display_format'=> 'd.m.Y',
                        'return_format' => 'Y-m-d',
                        'wrapper'       => ['width' => '20'],
                    ],
                    [
                        'key'          => 'field_termin_ort',
                        'label'        => 'Ort',
                        'name'         => 'ort',
                        'type'         => 'text',
                        'placeholder'  => 'z. B. München oder Online',
                        'wrapper'      => ['width' => '35'],
                    ],
                    [
                        'key'          => 'field_termin_ausgebucht',
                        'label'        => 'Ausgebucht?',
                        'name'         => 'ausgebucht',
                        'type'         => 'true_false',
                        'ui'           => 1,
                        'default_value'=> 0,
                        'wrapper'      => ['width' => '25'],
                    ],
                ],
            ],

            // ── Tab 5: Buchung & CTA ───────────────────────────────────────
            [
                'key'   => 'field_tab_cta',
                'label' => '🔗 Buchung',
                'name'  => '',
                'type'  => 'tab',
            ],

            [
                'key'           => 'field_kurs_cta_label',
                'label'         => 'Button-Text',
                'name'          => 'cta_label',
                'type'          => 'text',
                'instructions'  => 'Text auf dem Anmelde-Button. Leer lassen → "Jetzt anmelden"',
                'placeholder'   => 'Jetzt anmelden',
                'default_value' => 'Jetzt anmelden',
                'wrapper'       => ['width' => '40'],
            ],
            [
                'key'          => 'field_kurs_cta_url',
                'label'        => 'Buchungs-URL',
                'name'         => 'cta_url',
                'type'         => 'url',
                'instructions' => 'Link zum Buchungssystem oder Kontaktformular. Leer lassen → E-Mail-Fallback.',
                'placeholder'  => 'https://buchung.beispiel.de/kurs-id',
                'wrapper'      => ['width' => '60'],
            ],

        ],
    ]);
    }); // end acf/init
} // end if(false)

// ═══════════════════════════════════════════════════════════════════════════════
// NATIVE WORDPRESS META BOXES — kein Plugin nötig
// Daten werden als JSON in post_meta gespeichert (_kurs_*)
// ═══════════════════════════════════════════════════════════════════════════════

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
            <p class="desc">Erscheint im Infokästchen „Termine / Ort" auf der Kursseite. Z. B. „Start jederzeit möglich, Termine werden mit den TeilnehmerInnen vereinbart."</p>
            <textarea name="kurs_termine_notiz" id="kurs_termine_notiz"
                      rows="3"
                      placeholder="z. B. Start jederzeit möglich, Termine und Veranstaltungsorte werden mit den TeilnehmerInnen vereinbart (teilweise in den eigenen Organisationen)."><?php echo esc_textarea(get_post_meta($post->ID, '_kurs_termine_notiz', true)); ?></textarea>
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

                // Zähler für eindeutige Array-Indizes
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
                        // Eingabefeld anhand des name-Attributs finden
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

// ─── Speicher-Funktion ────────────────────────────────────────────────────────

add_action('save_post_kurs', function (int $post_id): void {
    // Sicherheitschecks
    if (!isset($_POST['gfp_kurs_nonce'])) return;
    if (!wp_verify_nonce($_POST['gfp_kurs_nonce'], 'gfp_kurs_save')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    // Format
    $erlaubte_formate = ['academy', 'inhouse', 'coaching', 'consulting'];
    $format = sanitize_text_field($_POST['kurs_format'] ?? '');
    if (in_array($format, $erlaubte_formate, true)) {
        update_post_meta($post_id, '_kurs_format', $format);
    }

    // Lernziele
    $lernziele_raw = wp_unslash($_POST['kurs_lernziele'] ?? []);
    $lernziele = array_values(array_filter(array_map('sanitize_text_field', (array) $lernziele_raw)));
    update_post_meta($post_id, '_kurs_lernziele', wp_json_encode($lernziele, JSON_UNESCAPED_UNICODE));

    // Trainer
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

    // Termine
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
    // Nach Datum sortieren
    usort($termine, fn($a, $b) => strcmp($a['datum'], $b['datum']));
    update_post_meta($post_id, '_kurs_termine', wp_json_encode($termine, JSON_UNESCAPED_UNICODE));

    // Terminnotiz
    update_post_meta($post_id, '_kurs_termine_notiz', sanitize_textarea_field(wp_unslash($_POST['kurs_termine_notiz'] ?? '')));

    // CTA
    update_post_meta($post_id, '_kurs_cta_label', sanitize_text_field($_POST['kurs_cta_label'] ?? ''));
    update_post_meta($post_id, '_kurs_cta_url',   esc_url_raw($_POST['kurs_cta_url']           ?? ''));

    // Infokästchen
    update_post_meta($post_id, '_kurs_teilnehmer',      sanitize_text_field($_POST['kurs_teilnehmer']      ?? ''));
    update_post_meta($post_id, '_kurs_investition',     sanitize_text_field($_POST['kurs_investition']     ?? ''));
    update_post_meta($post_id, '_kurs_voraussetzungen', sanitize_textarea_field($_POST['kurs_voraussetzungen'] ?? ''));
    update_post_meta($post_id, '_kurs_infoabend',       sanitize_text_field($_POST['kurs_infoabend']       ?? ''));
});

// ─── Hilfsfunktion: Kurs-Metadaten lesen ─────────────────────────────────────

function gfp_get_kurs_meta(int $post_id, string $field): mixed {
    return match($field) {
        'format'    => get_post_meta($post_id, '_kurs_format',    true) ?: '',
        'lernziele' => json_decode(get_post_meta($post_id, '_kurs_lernziele', true) ?: '[]', true) ?: [],
        'trainer'   => json_decode(get_post_meta($post_id, '_kurs_trainer',   true) ?: '[]', true) ?: [],
        'termine'   => json_decode(get_post_meta($post_id, '_kurs_termine',   true) ?: '[]', true) ?: [],
        'cta_label'        => get_post_meta($post_id, '_kurs_cta_label',        true) ?: 'Jetzt anmelden',
        'cta_url'          => get_post_meta($post_id, '_kurs_cta_url',          true) ?: '',
        'teilnehmer'       => get_post_meta($post_id, '_kurs_teilnehmer',       true) ?: '',
        'investition'      => get_post_meta($post_id, '_kurs_investition',      true) ?: '',
        'voraussetzungen'  => get_post_meta($post_id, '_kurs_voraussetzungen',  true) ?: '',
        'infoabend'        => get_post_meta($post_id, '_kurs_infoabend',        true) ?: '',
        'termine_notiz'    => get_post_meta($post_id, '_kurs_termine_notiz',    true) ?: '',
        default            => '',
    };
}

// ─── Hilfsfunktionen für Templates ───────────────────────────────────────────

/**
 * Gibt den Hex-Farbwert für einen Farbnamen zurück.
 */
function gfp_farbe_hex(string $farbe): string {
    return match($farbe) {
        'blue'   => '#0073BC',
        'teal'   => '#23B0A5',
        'orange' => '#F18712',
        'purple' => '#9E4493',
        'sky'    => '#50C1E0',
        default  => '#0073BC',
    };
}

/**
 * Gibt den Fachbereich (WP_Term) des aktuellen Kurses zurück, oder false.
 */
function gfp_get_fachbereich(int $post_id = 0): WP_Term|false {
    $post_id = $post_id ?: get_the_ID();
    $terms   = get_the_terms($post_id, 'fachbereich');
    return ($terms && !is_wp_error($terms)) ? $terms[0] : false;
}

/**
 * Gibt die Farbe (z.B. "blue") des Fachbereichs eines Kurses zurück.
 */
function gfp_get_kurs_farbe(int $post_id = 0): string {
    $fachbereich = gfp_get_fachbereich($post_id);
    if (!$fachbereich) return 'blue';
    return get_term_meta($fachbereich->term_id, 'farbe', true) ?: 'blue';
}

/**
 * Formatiert ein Datum (Y-m-d) auf Deutsch: "15. September 2025"
 */
function gfp_format_datum(string $iso, string $iso_bis = ''): string {
    if (!$iso) return '';
    $fmt = new IntlDateFormatter('de_DE', IntlDateFormatter::LONG, IntlDateFormatter::NONE);
    $von = $fmt->format(new DateTime($iso));
    if ($iso_bis) {
        return $von . ' – ' . $fmt->format(new DateTime($iso_bis));
    }
    return $von;
}

/**
 * Gibt die kommenden Termine (ab heute) zurück.
 */
function gfp_kommende_termine(array $termine): array {
    $heute = date('Y-m-d');
    return array_filter($termine, fn($t) => ($t['datum'] ?? '') >= $heute);
}

/**
 * Format-Labels
 */
function gfp_format_label(string $format): string {
    return match($format) {
        'academy'    => 'Academy — offenes Seminar',
        'inhouse'    => 'Inhouse — firmeninternes Training',
        'coaching'   => 'Coaching',
        'consulting' => 'Consulting',
        default      => $format,
    };
}

// ═══════════════════════════════════════════════════════════════════════════════
// STARTSEITEN-INHALTE — Eigene Admin-Seite (admin-post.php Pattern)
// Menü: 🏠 Startseite → admin.php?page=gfp-startseite
// ═══════════════════════════════════════════════════════════════════════════════

// ── „Seite bearbeiten"-Button in der Admin-Bar zur Startseiten-Seite umleiten ─
add_action('admin_bar_menu', function (WP_Admin_Bar $bar): void {
    if (!is_front_page() || !is_user_logged_in()) return;
    $node = $bar->get_node('edit');
    if (!$node) return;
    $bar->add_node([
        'id'   => 'edit',
        'href' => admin_url('admin.php?page=gfp-startseite'),
    ]);
}, 100);

// ── Menüpunkt registrieren ────────────────────────────────────────────────────
add_action('admin_menu', function(): void {
    add_menu_page(
        'Startseite bearbeiten',
        '🏠 Startseite',
        'edit_pages',
        'gfp-startseite',
        'gfp_render_startseite_page',
        'dashicons-home',
        3
    );
});

// ── Gemeinsame Speicherfunktion ───────────────────────────────────────────────
function gfp_do_save_startseite(): void {
    $text_fields = [
        'hp_hero_eyebrow', 'hp_hero_sub', 'hp_hero_cta_label',
        'hp_cta_headline_l1', 'hp_cta_headline_l2',
        'hp_cta_label', 'hp_testi_author', 'hp_stats_label',
        'hp_stat_1_n', 'hp_stat_1_d', 'hp_stat_2_n', 'hp_stat_2_d', 'hp_stat_3_n', 'hp_stat_3_d',
        'hp_trainers_h1', 'hp_trainers_h2', 'hp_ft_brand_claim', 'hp_ft_email',
        'hp_alfred_badge', 'hp_alfred_intro_h', 'hp_alfred_intro_right_strong', 'hp_alfred_intro_right_sub',
        'hp_alfred_main_h', 'hp_alfred_step1_t', 'hp_alfred_step1_s',
        'hp_alfred_step2_t', 'hp_alfred_step2_s', 'hp_alfred_step3_t', 'hp_alfred_step3_s',
        'hp_marquee_label',
    ];
    for ($i = 1; $i <= 5; $i++) {
        $text_fields[] = "hp_fb_{$i}_num";
        $text_fields[] = "hp_fb_{$i}_tag";
        $text_fields[] = "hp_fb_{$i}_titel";
        $text_fields[] = "hp_fb_{$i}_farbe";
    }
    for ($n = 1; $n <= 8; $n++) {
        $text_fields[] = "hp_regel_{$n}_t";
        $text_fields[] = "hp_regel_{$n}_b";
    }
    foreach ($text_fields as $f) {
        $val = $_POST[$f] ?? '';
        if (is_array($val)) $val = '';
        update_option('gfp_' . $f, sanitize_text_field(wp_unslash($val)));
    }

    $textarea_fields = [
        'hp_hero_titel', 'hp_hero_text', 'hp_problem_text', 'hp_cta_text', 'hp_testi_quote',
        'hp_manifest_intro', 'hp_trainers_sub', 'hp_ft_brand_text',
        'hp_alfred_intro_p', 'hp_alfred_main_p', 'hp_alfred_opening_msg', 'hp_alfred_chips',
        'hp_marquee_items', 'hp_clients', 'hp_bilder',
    ];
    for ($i = 1; $i <= 5; $i++) {
        $textarea_fields[] = "hp_fb_{$i}_text";
    }
    foreach ($textarea_fields as $f) {
        $val = $_POST[$f] ?? '';
        if (is_array($val)) $val = '';
        update_option('gfp_' . $f, sanitize_textarea_field(wp_unslash($val)));
    }

    foreach (['hp_hero_cta_url', 'hp_ft_linkedin', 'hp_ft_instagram', 'hp_ft_youtube'] as $f) {
        $val = $_POST[$f] ?? '';
        if (is_array($val)) $val = '';
        update_option('gfp_' . $f, esc_url_raw(wp_unslash($val)));
    }

    // Fachbereich-Farben auch auf Taxonomy-Terms schreiben
    $erlaubte_farben = ['blue', 'teal', 'orange', 'purple', 'sky'];
    for ($i = 1; $i <= 5; $i++) {
        $farbe = sanitize_text_field(wp_unslash($_POST["hp_fb_{$i}_farbe"] ?? ''));
        $titel = sanitize_text_field(wp_unslash($_POST["hp_fb_{$i}_titel"] ?? ''));
        if ($farbe && in_array($farbe, $erlaubte_farben, true) && $titel) {
            $term = get_term_by('name', $titel, 'fachbereich');
            if ($term) update_term_meta($term->term_id, 'farbe', $farbe);
        }
    }
}

// ── AJAX-Handler (aufgerufen vom JS-Frontend) ─────────────────────────────────
add_action('wp_ajax_gfp_hp_save', function(): void {
    check_ajax_referer('gfp_startseite_save', '_gfp_startseite_nonce');
    if (!current_user_can('edit_pages')) {
        wp_send_json_error('Keine Berechtigung.');
    }
    gfp_do_save_startseite();
    wp_send_json_success('Gespeichert');
});

function gfp_render_startseite_page(): void {
    if (!current_user_can('edit_pages')) {
        wp_die('Keine Berechtigung.');
    }

    // Speichern bei normalem Form-POST (kein JS nötig)
    $saved = false;
    if (
        !empty($_POST['_gfp_startseite_nonce']) &&
        wp_verify_nonce(wp_unslash($_POST['_gfp_startseite_nonce']), 'gfp_startseite_save')
    ) {
        gfp_do_save_startseite();
        $saved = true;
    }

    $m = fn(string $key, string $fb = '') => get_option('gfp_hp_' . $key, '') ?: $fb;

    // ── Hero ──────────────────────────────────────────────────────────────
    $hero_eyebrow   = $m('hero_eyebrow',   'Gesellschaft für Personalentwicklung');
    $hero_titel     = $m('hero_titel',     'SUPERHEROES,<br>LIKE US.');
    $hero_sub       = $m('hero_sub',       'Nicht unbesiegbar. Nur unaufhaltbar.');
    $hero_text      = $m('hero_text',      'Wir entwickeln Menschen, Teams und Organisationen – mit Klarheit, Haltung und Wirkung.');
    $hero_cta_label = $m('hero_cta_label', 'Unsere Programme');
    $hero_cta_url   = $m('hero_cta_url',   '');

    // ── Abschnitte ────────────────────────────────────────────────────────
    $problem_text   = $m('problem_text',   'Die Welt ist komplex. Organisationen auch. Wir helfen euch, den Überblick zu behalten – und wirksam zu handeln. Mit Erfahrung, Methode und echter Leidenschaft.');
    $cta_headline_l1 = $m('cta_headline_l1', 'DO YOU FEEL');
    $cta_headline_l2 = $m('cta_headline_l2', 'LIKE A HERO?');
    $cta_text       = $m('cta_text',       'Dann lass uns gemeinsam herausfinden, welches GfP-Programm zu euch passt.');
    $cta_label      = $m('cta_label',      'Jetzt Programm finden');
    $testi_quote    = $m('testi_quote',    '„GfP hat uns nicht nur trainiert – sie haben uns gezeigt, was wirklich möglich ist."');
    $testi_author   = $m('testi_author',   'Anna Müller · Head of People, TechCorp GmbH');

    // ── Zahlen ────────────────────────────────────────────────────────────
    $stats_label = $m('stats_label', 'GfP in Zahlen');
    $stat_1_n = $m('stat_1_n', '30+');
    $stat_1_d = $m('stat_1_d', 'Jahre Erfahrung in Personalentwicklung und Training');
    $stat_2_n = $m('stat_2_n', '5.000+');
    $stat_2_d = $m('stat_2_d', 'Teilnehmende pro Jahr in Academy und Inhouse-Programmen');
    $stat_3_n = $m('stat_3_n', '98%');
    $stat_3_d = $m('stat_3_d', 'Weiterempfehlungsrate unserer Teilnehmenden');

    // ── Fachbereiche (5 Karten) ───────────────────────────────────────────
    $fb_defaults = [
        1 => ['01', 'Führung & Entwicklung', 'Leadership & Führung',           'Führung, die wirkt. Nicht durch Kontrolle, sondern durch Klarheit und Haltung.',           'blue'],
        2 => ['02', 'Workshopdesign',         'Facilitation & Moderation',      'Meetings, die Energie geben statt nehmen. Workshops mit echten Ergebnissen.',             'teal'],
        3 => ['03', 'Teamentwicklung',         'Teams & Collaboration',          'Teams, die mehr sind als die Summe ihrer Teile. Zusammenarbeit, die trägt.',             'orange'],
        4 => ['04', 'Wandel gestalten',        'Organisation & Transformation',  'Veränderung als Chance. Strukturen, die sich anpassen. Menschen, die mitgehen.',         'purple'],
        5 => ['05', 'Persönlichkeit',          'Personality & Skills',           'Kompetenzen, die bleiben. Selbstwirksamkeit, die trägt.',                                 'sky'],
    ];
    $fb_fields = [];
    foreach ($fb_defaults as $i => [$d_num, $d_tag, $d_titel, $d_text, $d_farbe]) {
        $titel = $m("fb_{$i}_titel", $d_titel);
        // Farbe: admin-Einstellung → taxonomy-Term → Standardwert
        $farbe_saved = $m("fb_{$i}_farbe", '');
        if (!$farbe_saved) {
            $term = get_term_by('name', $titel, 'fachbereich');
            $farbe_saved = $term ? (get_term_meta($term->term_id, 'farbe', true) ?: $d_farbe) : $d_farbe;
        }
        $fb_fields[$i] = [
            'num'   => $m("fb_{$i}_num",   $d_num),
            'tag'   => $m("fb_{$i}_tag",   $d_tag),
            'titel' => $titel,
            'text'  => $m("fb_{$i}_text",  $d_text),
            'farbe' => $farbe_saved,
        ];
    }

    // ── Manifest ──────────────────────────────────────────────────────────
    $manifest_intro = $m('manifest_intro', 'Was uns antreibt. Was uns ausmacht. Was wir glauben, wenn es um Entwicklung von Menschen und Organisationen geht.');
    $regel_defaults = [
        ['Klarheit vor Komplexität',  'Wir vereinfachen, ohne zu verflachen.'],
        ['Haltung zeigen',            'Wir stehen für etwas. Auch wenn es unbequem ist.'],
        ['Wirkung statt Wellness',    'Entwicklung, die nachhallt. Nicht nur im Seminarraum.'],
        ['Menschen ernst nehmen',     'Kein Babysitting. Echte Auseinandersetzung.'],
        ['Weniger ist mehr',          'Lieber ein Thema wirklich durchdringen als vieles streifen.'],
        ['Systeme denken',            'Individuen sind Teil von Systemen. Wir vergessen das nie.'],
        ['Humor erlaubt',             'Ernst nehmen ≠ humorlos sein. Lachen öffnet Türen.'],
        ['Superhero-Mindset',         'Nicht unbesiegbar. Nur unaufhaltbar. Wie wir alle.'],
    ];
    $regeln_fields = [];
    foreach ($regel_defaults as $i => [$d_t, $d_b]) {
        $n = $i + 1;
        $regeln_fields[$n] = [
            't' => $m("regel_{$n}_t", $d_t),
            'b' => $m("regel_{$n}_b", $d_b),
        ];
    }

    // ── Trainer-Sektion ───────────────────────────────────────────────────
    $trainers_h1  = $m('trainers_h1',  'Unser');
    $trainers_h2  = $m('trainers_h2',  'Team');
    $trainers_sub = $m('trainers_sub', 'Erfahren, direkt, mit Haltung. Unsere Trainer bringen mit, was wirklich zählt.');

    // ── Bildstreifen ──────────────────────────────────────────────────────
    $bilder_raw = $m('bilder', '');

    // ── Alfred-Sektion ────────────────────────────────────────────────────
    $alfred_badge       = $m('alfred_badge',       'AL');
    $alfred_intro_h     = $m('alfred_intro_h',     'Meet ALFRED');
    $alfred_intro_p     = $m('alfred_intro_p',     'Dein persönlicher Guide zu deinem GfP-Format. Kein Cape, kein Bullshit — nur die richtigen Fragen.');
    $alfred_intro_right_strong = $m('alfred_intro_right_strong', 'Dein perfektes Format');
    $alfred_intro_right_sub    = $m('alfred_intro_right_sub',    '3 Schritte · 2 Minuten · 0 Unverbindlichkeiten');
    $alfred_main_h      = $m('alfred_main_h',      'DO YOU FEEL LIKE A HERO?');
    $alfred_main_p      = $m('alfred_main_p',      'Alfred findet heraus, was dich bewegt – und führt dich direkt zu dem Format, das wirklich passt. Nicht der Katalog. Dein Ding.');
    $alfred_step1_t     = $m('alfred_step1_t',     'Worum geht\'s dir gerade?');
    $alfred_step1_s     = $m('alfred_step1_s',     'Dein Anliegen als Ausgangspunkt');
    $alfred_step2_t     = $m('alfred_step2_t',     'Wie willst du arbeiten?');
    $alfred_step2_s     = $m('alfred_step2_s',     'Academy, Inhouse, Coaching oder Tools');
    $alfred_step3_t     = $m('alfred_step3_t',     'Dein perfektes Format');
    $alfred_step3_s     = $m('alfred_step3_s',     'Kuratiert — nicht der ganze Katalog');
    $alfred_opening_msg = $m('alfred_opening_msg', 'Guten Tag. Ich bin Alfred – kein Held, aber unverzichtbar. Worum geht\'s dir gerade?');
    // Schnellauswahl-Buttons (5 Stück, durch Zeilenumbruch getrennt gespeichert)
    $alfred_chips_raw   = $m('alfred_chips', "Ich will besser führen\nMein Team braucht Entwicklung\nWir stehen vor einer Veränderung\nMeetings und Workshops verbessern\nIch will persönlich wachsen");

    // ── Marquee ───────────────────────────────────────────────────────────
    $marquee_label     = $m('marquee_label',     'Weniger Bullshit, mehr Impact.');
    // Firmen/Begriffe, durch Zeilenumbruch getrennt
    $marquee_items_raw = $m('marquee_items',     "ADEG\nBestattung Wien\nBILLA\nInfineon\nBoehringer Ingelheim\nWiener Linien\nAustria Trend Hotels\nRaiffeisen");
    $clients_raw       = $m('clients',           "ADEG\nBestattung Wien\nBILLA\nInfineon\nBoehringer Ingelheim\nWiener Linien\nAustria Trend Hotels\nRaiffeisen");

    // ── Footer ────────────────────────────────────────────────────────────
    $ft_brand_claim = $m('ft_brand_claim', 'Superheroes, wie wir.');
    $ft_brand_text  = $m('ft_brand_text',  'Gesellschaft für Personalentwicklung — Wir entwickeln Menschen, Teams und Organisationen seit über 30 Jahren.');
    $ft_email       = $m('ft_email',       'info@gfp.de');
    $ft_linkedin    = $m('ft_linkedin',    '#');
    $ft_instagram   = $m('ft_instagram',   '#');
    $ft_youtube     = $m('ft_youtube',     '#');
    ?>
    <div class="wrap">
    <h1 style="display:flex;align-items:center;gap:10px;">🏠 Startseite bearbeiten</h1>

    <?php if ($saved) : ?>
        <div class="notice notice-success is-dismissible" style="margin:12px 0;">
            <p><strong>✅ Gespeichert!</strong> Die Änderungen wurden übernommen.</p>
        </div>
    <?php endif; ?>

    <form id="gfp-startseite-form" method="post" action="<?php echo esc_url(admin_url('admin.php?page=gfp-startseite')); ?>">
        <input type="hidden" name="page" value="gfp-startseite">
        <?php wp_nonce_field('gfp_startseite_save', '_gfp_startseite_nonce'); ?>

    <style>
    .gfp-hp .gfp-tabs  { display:flex; gap:4px; border-bottom:2px solid #ddd; margin-bottom:20px; flex-wrap:wrap; }
    .gfp-hp .gfp-tab   { padding:8px 16px; cursor:pointer; border:1px solid #ddd; border-bottom:none; background:#f9f9f9; font-size:13px; font-weight:600; border-radius:4px 4px 0 0; color:#555; }
    .gfp-hp .gfp-tab.active { background:#fff; border-bottom:2px solid #fff; margin-bottom:-2px; color:#111; }
    .gfp-hp .gfp-panel { display:none; } .gfp-hp .gfp-panel.active { display:block; }
    .gfp-hp .gfp-field { margin-bottom:18px; }
    .gfp-hp .gfp-field label { display:block; font-weight:600; font-size:13px; margin-bottom:5px; color:#333; }
    .gfp-hp .gfp-field .desc { font-size:12px; color:#888; margin-bottom:5px; }
    .gfp-hp .gfp-field input[type=text],
    .gfp-hp .gfp-field input[type=url],
    .gfp-hp .gfp-field input[type=email],
    .gfp-hp .gfp-field select,
    .gfp-hp .gfp-field textarea { width:100%; padding:8px 10px; border:1px solid #ccc; border-radius:4px; font-size:14px; box-sizing:border-box; }
    .gfp-hp .gfp-field textarea { min-height:80px; resize:vertical; }
    .gfp-hp .gfp-cols-2 { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
    .gfp-hp .gfp-stat-row { display:grid; grid-template-columns:120px 1fr; gap:12px; align-items:start; margin-bottom:16px; padding:12px; background:#f9f9f9; border:1px solid #e8e8e8; border-radius:6px; }
    .gfp-hp .gfp-stat-row label { font-size:12px; font-weight:700; color:#555; text-transform:uppercase; margin-bottom:4px; }
    .gfp-hp .stat-num input { font-size:22px; font-weight:700; text-align:center; padding:10px; }
    .gfp-hp .gfp-fb-card { border:1px solid #e0e0e0; border-left-width:4px; border-radius:6px; padding:14px; margin-bottom:14px; background:#fafafa; }
    .gfp-hp .gfp-fb-card h4 { margin:0 0 10px; font-size:13px; font-weight:700; color:#444; text-transform:uppercase; letter-spacing:.04em; }
    .gfp-hp .gfp-regel-row { display:grid; grid-template-columns:36px 1fr 1fr; gap:10px; align-items:start; margin-bottom:10px; padding:10px; background:#f9f9f9; border:1px solid #e8e8e8; border-radius:4px; }
    .gfp-hp .gfp-regel-n { font-size:18px; font-weight:700; color:#bbb; line-height:36px; text-align:center; }
    </style>

    <div class="gfp-hp">

        <div class="gfp-tabs">
            <div class="gfp-tab active" data-panel="hp-hero">🦸 Hero</div>
            <div class="gfp-tab" data-panel="hp-alfred">🤖 Alfred</div>
            <div class="gfp-tab" data-panel="hp-marquee">📣 Marquee</div>
            <div class="gfp-tab" data-panel="hp-bilder">🖼 Bildstreifen</div>
            <div class="gfp-tab" data-panel="hp-abschnitte">📝 Abschnitte</div>
            <div class="gfp-tab" data-panel="hp-zahlen">📊 Zahlen</div>
            <div class="gfp-tab" data-panel="hp-fachbereiche">🎯 Fachbereiche</div>
            <div class="gfp-tab" data-panel="hp-manifest">📜 Manifest</div>
            <div class="gfp-tab" data-panel="hp-trainer">👥 Trainer</div>
            <div class="gfp-tab" data-panel="hp-footer">🔗 Footer</div>
        </div>

        <!-- ── Hero ── -->
        <div class="gfp-panel active" id="panel-hp-hero">
            <div class="gfp-field">
                <label for="hp_hero_eyebrow">Eyebrow (Zeile über dem Titel)</label>
                <input type="text" name="hp_hero_eyebrow" id="hp_hero_eyebrow"
                       value="<?php echo esc_attr($hero_eyebrow); ?>"
                       placeholder="Gesellschaft für Personalentwicklung">
            </div>
            <div class="gfp-field">
                <label for="hp_hero_titel">Haupt-Titel</label>
                <p class="desc">HTML erlaubt für Zeilenumbrüche, z.&nbsp;B. <code>&lt;br&gt;</code></p>
                <textarea name="hp_hero_titel" id="hp_hero_titel"
                          rows="2"><?php echo esc_textarea($hero_titel); ?></textarea>
            </div>
            <div class="gfp-field">
                <label for="hp_hero_sub">Subline (unter dem Titel)</label>
                <input type="text" name="hp_hero_sub" id="hp_hero_sub"
                       value="<?php echo esc_attr($hero_sub); ?>"
                       placeholder="Nicht unbesiegbar. Nur unaufhaltbar.">
            </div>
            <div class="gfp-field">
                <label for="hp_hero_text">Beschreibungstext</label>
                <textarea name="hp_hero_text" id="hp_hero_text"><?php echo esc_textarea($hero_text); ?></textarea>
            </div>
            <div class="gfp-cols-2">
                <div class="gfp-field">
                    <label for="hp_hero_cta_label">Button-Text</label>
                    <input type="text" name="hp_hero_cta_label" id="hp_hero_cta_label"
                           value="<?php echo esc_attr($hero_cta_label); ?>"
                           placeholder="Unsere Programme">
                </div>
                <div class="gfp-field">
                    <label for="hp_hero_cta_url">Button-URL</label>
                    <p class="desc">Leer lassen → automatisch zur Kursübersicht</p>
                    <input type="url" name="hp_hero_cta_url" id="hp_hero_cta_url"
                           value="<?php echo esc_attr($hero_cta_url); ?>"
                           placeholder="https://…">
                </div>
            </div>
        </div>

        <!-- ── Alfred ── -->
        <div class="gfp-panel" id="panel-hp-alfred">
            <p style="font-size:13px;color:#666;margin-bottom:16px;">Texte der Alfred-Chatbot-Sektion.</p>
            <p style="font-size:13px;font-weight:700;color:#444;margin-bottom:10px;">Intro-Leiste (oben)</p>
            <div class="gfp-cols-2">
                <div class="gfp-field">
                    <label>Badge-Text</label>
                    <input type="text" name="hp_alfred_badge"
                           value="<?php echo esc_attr($alfred_badge); ?>" placeholder="AL">
                </div>
                <div class="gfp-field">
                    <label>Überschrift</label>
                    <input type="text" name="hp_alfred_intro_h"
                           value="<?php echo esc_attr($alfred_intro_h); ?>" placeholder="Meet ALFRED">
                </div>
            </div>
            <div class="gfp-field">
                <label>Intro-Text (links)</label>
                <textarea name="hp_alfred_intro_p" rows="2"><?php echo esc_textarea($alfred_intro_p); ?></textarea>
            </div>
            <div class="gfp-cols-2">
                <div class="gfp-field">
                    <label>Rechte Seite — fetter Text</label>
                    <input type="text" name="hp_alfred_intro_right_strong"
                           value="<?php echo esc_attr($alfred_intro_right_strong); ?>">
                </div>
                <div class="gfp-field">
                    <label>Rechte Seite — kleiner Text</label>
                    <input type="text" name="hp_alfred_intro_right_sub"
                           value="<?php echo esc_attr($alfred_intro_right_sub); ?>">
                </div>
            </div>
            <hr style="margin:20px 0;border-color:#eee;">
            <p style="font-size:13px;font-weight:700;color:#444;margin-bottom:10px;">Hauptbereich (links)</p>
            <div class="gfp-field">
                <label>Headline</label>
                <input type="text" name="hp_alfred_main_h"
                       value="<?php echo esc_attr($alfred_main_h); ?>" placeholder="DO YOU FEEL LIKE A HERO?">
            </div>
            <div class="gfp-field">
                <label>Beschreibungstext</label>
                <textarea name="hp_alfred_main_p" rows="2"><?php echo esc_textarea($alfred_main_p); ?></textarea>
            </div>
            <div class="gfp-cols-2">
                <div class="gfp-field"><label>Schritt 1 — Titel</label><input type="text" name="hp_alfred_step1_t" value="<?php echo esc_attr($alfred_step1_t); ?>"></div>
                <div class="gfp-field"><label>Schritt 1 — Untertitel</label><input type="text" name="hp_alfred_step1_s" value="<?php echo esc_attr($alfred_step1_s); ?>"></div>
                <div class="gfp-field"><label>Schritt 2 — Titel</label><input type="text" name="hp_alfred_step2_t" value="<?php echo esc_attr($alfred_step2_t); ?>"></div>
                <div class="gfp-field"><label>Schritt 2 — Untertitel</label><input type="text" name="hp_alfred_step2_s" value="<?php echo esc_attr($alfred_step2_s); ?>"></div>
                <div class="gfp-field"><label>Schritt 3 — Titel</label><input type="text" name="hp_alfred_step3_t" value="<?php echo esc_attr($alfred_step3_t); ?>"></div>
                <div class="gfp-field"><label>Schritt 3 — Untertitel</label><input type="text" name="hp_alfred_step3_s" value="<?php echo esc_attr($alfred_step3_s); ?>"></div>
            </div>
            <hr style="margin:20px 0;border-color:#eee;">
            <p style="font-size:13px;font-weight:700;color:#444;margin-bottom:10px;">Chat</p>
            <div class="gfp-field">
                <label>Erste Nachricht von Alfred</label>
                <textarea name="hp_alfred_opening_msg" rows="2"><?php echo esc_textarea($alfred_opening_msg); ?></textarea>
            </div>
            <div class="gfp-field">
                <label>Schnellauswahl-Buttons</label>
                <p class="desc">Ein Button pro Zeile (max. 5)</p>
                <textarea name="hp_alfred_chips" rows="6"><?php echo esc_textarea($alfred_chips_raw); ?></textarea>
            </div>
        </div>

        <!-- ── Marquee ── -->
        <div class="gfp-panel" id="panel-hp-marquee">
            <div class="gfp-field">
                <label for="hp_marquee_label">Label (über dem Laufband)</label>
                <input type="text" name="hp_marquee_label" id="hp_marquee_label"
                       value="<?php echo esc_attr($marquee_label); ?>"
                       placeholder="Weniger Bullshit, mehr Impact.">
            </div>
            <div class="gfp-field">
                <label for="hp_marquee_items">Laufband-Einträge</label>
                <p class="desc">Ein Eintrag pro Zeile — erscheint endlos scrollend</p>
                <textarea name="hp_marquee_items" id="hp_marquee_items"
                          rows="8"><?php echo esc_textarea($marquee_items_raw); ?></textarea>
            </div>
            <div class="gfp-field">
                <label for="hp_clients">Kunden-Logos / Firmennamen</label>
                <p class="desc">Ein Name pro Zeile — erscheint als Logozeile unter dem Band</p>
                <textarea name="hp_clients" id="hp_clients"
                          rows="8"><?php echo esc_textarea($clients_raw); ?></textarea>
            </div>
        </div>

        <!-- ── Bildstreifen ── -->
        <div class="gfp-panel" id="panel-hp-bilder">
            <p style="font-size:13px;color:#666;margin-bottom:16px;">
                Der scrollende Bildstreifen zwischen Fachbereiche und Trainer.<br>
                Ein Bild pro Zeile im Format: <code>https://…/bild.jpg</code> oder <code>https://…/bild.jpg|Bildunterschrift</code>
            </p>
            <div class="gfp-field">
                <label for="hp_bilder">Bilder (URL | Beschriftung)</label>
                <textarea name="hp_bilder" id="hp_bilder"
                          rows="10" style="font-family:monospace;font-size:12px;"><?php echo esc_textarea($bilder_raw); ?></textarea>
            </div>
            <p style="font-size:12px;color:#999;">
                Tipp: Lade Bilder zuerst in die WordPress-Mediathek hoch, klicke dort auf ein Bild und kopiere die URL aus „Datei-URL".
            </p>
        </div>

        <!-- ── Abschnitte ── -->
        <div class="gfp-panel" id="panel-hp-abschnitte">
            <div class="gfp-field">
                <label for="hp_problem_text">„Wir schaffen Klarheit" — Beschreibungstext</label>
                <textarea name="hp_problem_text" id="hp_problem_text"><?php echo esc_textarea($problem_text); ?></textarea>
            </div>
            <hr style="margin:20px 0;border-color:#eee;">
            <p style="font-size:13px;font-weight:700;color:#444;margin-bottom:12px;">CTA-Abschnitt (dunkle Sektion)</p>
            <div class="gfp-cols-2">
                <div class="gfp-field">
                    <label for="hp_cta_headline_l1">Headline — Zeile 1</label>
                    <input type="text" name="hp_cta_headline_l1" id="hp_cta_headline_l1"
                           value="<?php echo esc_attr($cta_headline_l1); ?>"
                           placeholder="DO YOU FEEL">
                </div>
                <div class="gfp-field">
                    <label for="hp_cta_headline_l2">Headline — Zeile 2</label>
                    <input type="text" name="hp_cta_headline_l2" id="hp_cta_headline_l2"
                           value="<?php echo esc_attr($cta_headline_l2); ?>"
                           placeholder="LIKE A HERO?">
                </div>
            </div>
            <div class="gfp-field">
                <label for="hp_cta_text">Text unter der Headline</label>
                <textarea name="hp_cta_text" id="hp_cta_text"><?php echo esc_textarea($cta_text); ?></textarea>
            </div>
            <div class="gfp-field">
                <label for="hp_cta_label">Button-Text</label>
                <input type="text" name="hp_cta_label" id="hp_cta_label"
                       value="<?php echo esc_attr($cta_label); ?>"
                       placeholder="Jetzt Programm finden">
            </div>
            <hr style="margin:20px 0;border-color:#eee;">
            <p style="font-size:13px;font-weight:700;color:#444;margin-bottom:12px;">Testimonial</p>
            <div class="gfp-field">
                <label for="hp_testi_quote">Zitat</label>
                <textarea name="hp_testi_quote" id="hp_testi_quote"><?php echo esc_textarea($testi_quote); ?></textarea>
            </div>
            <div class="gfp-field">
                <label for="hp_testi_author">Person</label>
                <input type="text" name="hp_testi_author" id="hp_testi_author"
                       value="<?php echo esc_attr($testi_author); ?>"
                       placeholder="Vorname Nachname · Rolle, Unternehmen">
            </div>
        </div>

        <!-- ── Zahlen ── -->
        <div class="gfp-panel" id="panel-hp-zahlen">
            <div class="gfp-field" style="margin-bottom:24px;">
                <label for="hp_stats_label">Abschnitts-Überschrift</label>
                <input type="text" name="hp_stats_label" id="hp_stats_label"
                       value="<?php echo esc_attr($stats_label); ?>"
                       placeholder="GfP in Zahlen">
            </div>
            <p style="font-size:13px;color:#666;margin-bottom:16px;">Die drei Kennzahlen.</p>
            <?php foreach ([
                ['1', $stat_1_n, $stat_1_d],
                ['2', $stat_2_n, $stat_2_d],
                ['3', $stat_3_n, $stat_3_d],
            ] as [$nr, $n, $d]) : ?>
            <div class="gfp-stat-row">
                <div class="stat-num">
                    <label>Zahl</label>
                    <input type="text" name="hp_stat_<?php echo $nr; ?>_n"
                           value="<?php echo esc_attr($n); ?>" placeholder="z. B. 30+">
                </div>
                <div>
                    <label>Beschreibung</label>
                    <input type="text" name="hp_stat_<?php echo $nr; ?>_d"
                           value="<?php echo esc_attr($d); ?>"
                           placeholder="Jahre Erfahrung …">
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- ── Fachbereiche ── -->
        <div class="gfp-panel" id="panel-hp-fachbereiche">
            <p style="font-size:13px;color:#666;margin-bottom:16px;">
                Die fünf Fachbereichs-Karten auf der Startseite. Die <strong>Farbe</strong> gilt gleichzeitig für alle Kurs-Karten und Kursseiten dieser Kategorie.
            </p>
            <?php
            $farben_opts = [
                'blue'   => 'Blau — Leadership & Führung (#0073BC)',
                'teal'   => 'Türkis — Facilitation & Moderation (#23B0A5)',
                'orange' => 'Orange — Teams & Collaboration (#F18712)',
                'purple' => 'Lila — Organisation & Transformation (#9E4493)',
                'sky'    => 'Hellblau — Personality & Skills (#50C1E0)',
            ];
            $farben_preview = ['blue' => '#0073BC', 'teal' => '#23B0A5', 'orange' => '#F18712', 'purple' => '#9E4493', 'sky' => '#50C1E0'];
            ?>
            <?php foreach ($fb_fields as $i => $fb) : ?>
            <div class="gfp-fb-card" style="border-left:4px solid <?php echo esc_attr($farben_preview[$fb['farbe']] ?? '#aaa'); ?>;">
                <h4>Karte <?php echo $i; ?> — <span style="color:<?php echo esc_attr($farben_preview[$fb['farbe']] ?? '#aaa'); ?>"><?php echo esc_html($fb['titel']); ?></span></h4>
                <div class="gfp-cols-2">
                    <div class="gfp-field">
                        <label>Nummer</label>
                        <input type="text" name="hp_fb_<?php echo $i; ?>_num"
                               value="<?php echo esc_attr($fb['num']); ?>" placeholder="01">
                    </div>
                    <div class="gfp-field">
                        <label>Tag (kleine Zeile)</label>
                        <input type="text" name="hp_fb_<?php echo $i; ?>_tag"
                               value="<?php echo esc_attr($fb['tag']); ?>" placeholder="Führung & Entwicklung">
                    </div>
                </div>
                <div class="gfp-cols-2">
                    <div class="gfp-field">
                        <label>Titel (große Zeile)</label>
                        <input type="text" name="hp_fb_<?php echo $i; ?>_titel"
                               value="<?php echo esc_attr($fb['titel']); ?>" placeholder="Leadership & Führung">
                    </div>
                    <div class="gfp-field">
                        <label>🎨 Farbe <span style="font-weight:400;color:#888;">(gilt für Kurs-Karten &amp; Kursseiten)</span></label>
                        <select name="hp_fb_<?php echo $i; ?>_farbe">
                            <?php foreach ($farben_opts as $wert => $label) : ?>
                                <option value="<?php echo esc_attr($wert); ?>" <?php selected($fb['farbe'], $wert); ?>>
                                    <?php echo esc_html($label); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="gfp-field">
                    <label>Text</label>
                    <textarea name="hp_fb_<?php echo $i; ?>_text" rows="2"><?php echo esc_textarea($fb['text']); ?></textarea>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- ── Manifest ── -->
        <div class="gfp-panel" id="panel-hp-manifest">
            <div class="gfp-field" style="margin-bottom:24px;">
                <label for="hp_manifest_intro">Einleitungstext (unter „Unser Manifest")</label>
                <textarea name="hp_manifest_intro" id="hp_manifest_intro" rows="2"><?php echo esc_textarea($manifest_intro); ?></textarea>
            </div>
            <p style="font-size:13px;color:#666;margin-bottom:12px;">Die acht Regeln:</p>
            <?php foreach ($regeln_fields as $n => $regel) : ?>
            <div class="gfp-regel-row">
                <div class="gfp-regel-n"><?php echo sprintf('%02d', $n); ?></div>
                <div class="gfp-field" style="margin:0;">
                    <label style="margin-bottom:3px;">Titel</label>
                    <input type="text" name="hp_regel_<?php echo $n; ?>_t"
                           value="<?php echo esc_attr($regel['t']); ?>">
                </div>
                <div class="gfp-field" style="margin:0;">
                    <label style="margin-bottom:3px;">Beschreibung</label>
                    <input type="text" name="hp_regel_<?php echo $n; ?>_b"
                           value="<?php echo esc_attr($regel['b']); ?>">
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- ── Trainer ── -->
        <div class="gfp-panel" id="panel-hp-trainer">
            <p style="font-size:13px;color:#666;margin-bottom:16px;">Überschrift und Intro-Text der Trainer-Sektion.</p>
            <div class="gfp-cols-2">
                <div class="gfp-field">
                    <label for="hp_trainers_h1">Überschrift Zeile 1</label>
                    <input type="text" name="hp_trainers_h1" id="hp_trainers_h1"
                           value="<?php echo esc_attr($trainers_h1); ?>" placeholder="Unser">
                </div>
                <div class="gfp-field">
                    <label for="hp_trainers_h2">Überschrift Zeile 2</label>
                    <input type="text" name="hp_trainers_h2" id="hp_trainers_h2"
                           value="<?php echo esc_attr($trainers_h2); ?>" placeholder="Team">
                </div>
            </div>
            <div class="gfp-field">
                <label for="hp_trainers_sub">Einleitungstext</label>
                <textarea name="hp_trainers_sub" id="hp_trainers_sub" rows="2"><?php echo esc_textarea($trainers_sub); ?></textarea>
            </div>
            <p style="font-size:12px;color:#999;margin-top:8px;">Die Trainer-Profile selbst pflegst du unter <strong>Trainer</strong> im WordPress-Menü.</p>
        </div>

        <!-- ── Footer ── -->
        <div class="gfp-panel" id="panel-hp-footer">
            <div class="gfp-field">
                <label for="hp_ft_brand_claim">Claim (neben dem Logo)</label>
                <input type="text" name="hp_ft_brand_claim" id="hp_ft_brand_claim"
                       value="<?php echo esc_attr($ft_brand_claim); ?>" placeholder="Superheroes, wie wir.">
            </div>
            <div class="gfp-field">
                <label for="hp_ft_brand_text">Beschreibungstext</label>
                <textarea name="hp_ft_brand_text" id="hp_ft_brand_text" rows="2"><?php echo esc_textarea($ft_brand_text); ?></textarea>
            </div>
            <div class="gfp-field">
                <label for="hp_ft_email">Kontakt-E-Mail</label>
                <input type="email" name="hp_ft_email" id="hp_ft_email"
                       value="<?php echo esc_attr($ft_email); ?>" placeholder="info@gfp.de">
            </div>
            <hr style="margin:20px 0;border-color:#eee;">
            <p style="font-size:13px;font-weight:700;color:#444;margin-bottom:12px;">Social-Media-Links</p>
            <div class="gfp-field">
                <label for="hp_ft_linkedin">LinkedIn-URL</label>
                <input type="url" name="hp_ft_linkedin" id="hp_ft_linkedin"
                       value="<?php echo esc_attr($ft_linkedin); ?>" placeholder="https://linkedin.com/company/…">
            </div>
            <div class="gfp-field">
                <label for="hp_ft_instagram">Instagram-URL</label>
                <input type="url" name="hp_ft_instagram" id="hp_ft_instagram"
                       value="<?php echo esc_attr($ft_instagram); ?>" placeholder="https://instagram.com/…">
            </div>
            <div class="gfp-field">
                <label for="hp_ft_youtube">YouTube-URL</label>
                <input type="url" name="hp_ft_youtube" id="hp_ft_youtube"
                       value="<?php echo esc_attr($ft_youtube); ?>" placeholder="https://youtube.com/@…">
            </div>
        </div>

    </div><!-- .gfp-hp -->

    <div style="margin-top:24px;display:flex;align-items:center;gap:16px;">
        <input type="submit" id="gfp-save-btn"
               class="button button-primary button-large"
               value="💾 Änderungen speichern">
        <span id="gfp-save-msg" style="display:none;font-size:14px;font-weight:600;"></span>
    </div>

    </form>
    </div><!-- .wrap -->

    <script>
    var GFP_NONCE = <?php echo json_encode(wp_create_nonce('gfp_startseite_save')); ?>;

    // Tab-Wechsel
    document.querySelectorAll('.gfp-hp .gfp-tab').forEach(function(tab) {
        tab.addEventListener('click', function() {
            document.querySelectorAll('.gfp-hp .gfp-tab').forEach(function(t) { t.classList.remove('active'); });
            document.querySelectorAll('.gfp-hp .gfp-panel').forEach(function(p) { p.classList.remove('active'); });
            tab.classList.add('active');
            document.getElementById('panel-' + tab.dataset.panel).classList.add('active');
        });
    });

    // AJAX-Speichern
    var gfpForm = document.getElementById('gfp-startseite-form');
    var gfpBtn  = document.getElementById('gfp-save-btn');
    var gfpMsg  = document.getElementById('gfp-save-msg');

    gfpForm.addEventListener('submit', function(e) {
        e.preventDefault();
        gfpBtn.disabled = true;
        gfpBtn.value = '⏳ Speichert…';
        gfpMsg.style.display = 'none';

        var data = new FormData(gfpForm);
        data.set('_gfp_startseite_nonce', GFP_NONCE);
        data.append('action', 'gfp_hp_save');

        fetch(ajaxurl, {
            method: 'POST',
            credentials: 'same-origin',
            body: data,
        })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            gfpBtn.disabled = false;
            gfpBtn.value = '💾 Änderungen speichern';
            gfpMsg.style.display = 'inline';
            if (res.success) {
                gfpMsg.style.color = '#00a32a';
                gfpMsg.textContent = '✅ Gespeichert!';
                setTimeout(function() { gfpMsg.style.display = 'none'; }, 3000);
            } else {
                gfpMsg.style.color = '#d63638';
                gfpMsg.textContent = '❌ ' + (res.data || 'Fehler beim Speichern');
            }
        })
        .catch(function(err) {
            gfpBtn.disabled = false;
            gfpBtn.value = '💾 Änderungen speichern';
            gfpMsg.style.display = 'inline';
            gfpMsg.style.color = '#d63638';
            gfpMsg.textContent = '❌ Netzwerkfehler: ' + (err.message || 'Unbekannter Fehler');
        });
    });
    </script>
    <?php
}

// ── Hilfsfunktion: Startseiten-Meta lesen ──────────────────────────────────────
function gfp_hp(string $field, string $fallback = ''): string {
    return get_option('gfp_hp_' . $field, '') ?: $fallback;
}

// ── Fachbereich-Kategorien mit korrekten Farben sicherstellen ─────────────────
// Läuft beim ersten Adminaufruf und legt fehlende Terms an.
// Fachbereich-Farben bei jedem Admin-Aufruf sicherstellen (idempotent, schnell)
add_action('admin_init', function(): void {
    $kategorien = [
        ['Leadership & Führung',          'blue'],
        ['Facilitation & Moderation',     'teal'],
        ['Teams & Collaboration',         'orange'],
        ['Organisation & Transformation', 'purple'],
        ['Personality & Skills',          'sky'],
    ];
    foreach ($kategorien as [$name, $farbe]) {
        $term = get_term_by('name', $name, 'fachbereich');
        if (!$term) {
            $result = wp_insert_term($name, 'fachbereich');
            if (!is_wp_error($result)) {
                update_term_meta($result['term_id'], 'farbe', $farbe);
            }
        } else {
            // Farbe nur setzen wenn sie falsch/leer ist
            $aktuelle = get_term_meta($term->term_id, 'farbe', true);
            if ($aktuelle !== $farbe) {
                update_term_meta($term->term_id, 'farbe', $farbe);
            }
        }
    }
});

// ══════════════════════════════════════════════════════════════════════════════
// KONTAKTSEITE — Meta Box + Formular-Handler
// ══════════════════════════════════════════════════════════════════════════════

// Meta Box nur auf Seiten mit dem Kontaktseite-Template anzeigen
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

    $text_fields = ['kp_headline', 'kp_sub', 'kp_phone', 'kp_form_heading'];
    foreach ($text_fields as $f) {
        update_post_meta($post_id, '_' . $f, sanitize_text_field(wp_unslash($_POST[$f] ?? '')));
    }
    $textarea_fields = ['kp_intro', 'kp_address'];
    foreach ($textarea_fields as $f) {
        update_post_meta($post_id, '_' . $f, sanitize_textarea_field(wp_unslash($_POST[$f] ?? '')));
    }
    update_post_meta($post_id, '_kp_email', sanitize_email(wp_unslash($_POST['kp_email'] ?? '')));
    // Map-Embed: nur iframe-Tag erlaubt
    $map_raw = wp_unslash($_POST['kp_map_embed'] ?? '');
    update_post_meta($post_id, '_kp_map_embed', wp_kses($map_raw, [
        'iframe' => ['src' => [], 'width' => [], 'height' => [], 'style' => [],
                     'allowfullscreen' => [], 'loading' => [], 'referrerpolicy' => [],
                     'frameborder' => [], 'title' => []],
    ]));
});

// ── Hilfsfunktion: Kontaktseiten-Meta lesen ────────────────────────────────────
function gfp_kp(string $field, string $fallback = ''): string {
    $id = (int) get_queried_object_id();
    return get_post_meta($id, '_kp_' . $field, true) ?: $fallback;
}

// ── Kontaktformular-Handler ────────────────────────────────────────────────────
add_action('admin_post_nopriv_gfp_contact_form', 'gfp_handle_contact_form');
add_action('admin_post_gfp_contact_form',        'gfp_handle_contact_form');

function gfp_handle_contact_form(): void {
    // CSRF-Prüfung
    if (empty($_POST['gfp_contact_nonce']) || !wp_verify_nonce($_POST['gfp_contact_nonce'], 'gfp_contact_send')) {
        wp_die('Ungültige Anfrage.', 403);
    }

    $page_id  = (int) ($_POST['_page_id'] ?? 0);
    $redirect = $page_id ? get_permalink($page_id) : home_url('/kontakt/');

    $name    = sanitize_text_field(wp_unslash($_POST['cf_name']    ?? ''));
    $email   = sanitize_email(wp_unslash($_POST['cf_email']        ?? ''));
    $subject = sanitize_text_field(wp_unslash($_POST['cf_subject'] ?? ''));
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
