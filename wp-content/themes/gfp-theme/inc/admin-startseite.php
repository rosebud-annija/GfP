<?php
defined('ABSPATH') || exit;

// ─── Admin-Bar: „Seite bearbeiten" zur Startseiten-Admin-Seite umleiten ───────

add_action('admin_bar_menu', function (WP_Admin_Bar $bar): void {
    if (!is_front_page() || !is_user_logged_in()) return;
    $node = $bar->get_node('edit');
    if (!$node) return;
    $bar->add_node([
        'id'   => 'edit',
        'href' => admin_url('admin.php?page=gfp-startseite'),
    ]);
}, 100);

// ─── Menüpunkt registrieren ───────────────────────────────────────────────────

add_action('admin_menu', function (): void {
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

// ─── Speicher-Funktion ────────────────────────────────────────────────────────

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

// ─── AJAX-Handler ─────────────────────────────────────────────────────────────

add_action('wp_ajax_gfp_hp_save', function (): void {
    check_ajax_referer('gfp_startseite_save', '_gfp_startseite_nonce');
    if (!current_user_can('edit_pages')) {
        wp_send_json_error('Keine Berechtigung.');
    }
    gfp_do_save_startseite();
    wp_send_json_success('Gespeichert');
});

// ─── Admin-Seite rendern ──────────────────────────────────────────────────────

function gfp_render_startseite_page(): void {
    if (!current_user_can('edit_pages')) {
        wp_die('Keine Berechtigung.');
    }

    $saved = false;
    if (
        !empty($_POST['_gfp_startseite_nonce']) &&
        wp_verify_nonce(wp_unslash($_POST['_gfp_startseite_nonce']), 'gfp_startseite_save')
    ) {
        gfp_do_save_startseite();
        $saved = true;
    }

    $m = fn(string $key, string $fb = '') => get_option('gfp_hp_' . $key, '') ?: $fb;

    $hero_eyebrow   = $m('hero_eyebrow',   'Gesellschaft für Personalentwicklung');
    $hero_titel     = $m('hero_titel',     'SUPERHEROES,<br>LIKE US.');
    $hero_sub       = $m('hero_sub',       'Nicht unbesiegbar. Nur unaufhaltbar.');
    $hero_text      = $m('hero_text',      'Wir entwickeln Menschen, Teams und Organisationen – mit Klarheit, Haltung und Wirkung.');
    $hero_cta_label = $m('hero_cta_label', 'Unsere Programme');
    $hero_cta_url   = $m('hero_cta_url',   '');

    $problem_text    = $m('problem_text',    'Die Welt ist komplex. Organisationen auch. Wir helfen euch, den Überblick zu behalten – und wirksam zu handeln. Mit Erfahrung, Methode und echter Leidenschaft.');
    $cta_headline_l1 = $m('cta_headline_l1', 'DO YOU FEEL');
    $cta_headline_l2 = $m('cta_headline_l2', 'LIKE A HERO?');
    $cta_text        = $m('cta_text',        'Dann lass uns gemeinsam herausfinden, welches GfP-Programm zu euch passt.');
    $cta_label       = $m('cta_label',       'Jetzt Programm finden');
    $testi_quote     = $m('testi_quote',     '„GfP hat uns nicht nur trainiert – sie haben uns gezeigt, was wirklich möglich ist."');
    $testi_author    = $m('testi_author',    'Anna Müller · Head of People, TechCorp GmbH');

    $stats_label = $m('stats_label', 'GfP in Zahlen');
    $stat_1_n = $m('stat_1_n', '30+');
    $stat_1_d = $m('stat_1_d', 'Jahre Erfahrung in Personalentwicklung und Training');
    $stat_2_n = $m('stat_2_n', '5.000+');
    $stat_2_d = $m('stat_2_d', 'Teilnehmende pro Jahr in Academy und Inhouse-Programmen');
    $stat_3_n = $m('stat_3_n', '98%');
    $stat_3_d = $m('stat_3_d', 'Weiterempfehlungsrate unserer Teilnehmenden');

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

    $trainers_h1  = $m('trainers_h1',  'Unser');
    $trainers_h2  = $m('trainers_h2',  'Team');
    $trainers_sub = $m('trainers_sub', 'Erfahren, direkt, mit Haltung. Unsere Trainer bringen mit, was wirklich zählt.');
    $bilder_raw   = $m('bilder',       '');

    $alfred_badge              = $m('alfred_badge',              'AL');
    $alfred_intro_h            = $m('alfred_intro_h',            'Meet ALFRED');
    $alfred_intro_p            = $m('alfred_intro_p',            'Dein persönlicher Guide zu deinem GfP-Format. Kein Cape, kein Bullshit — nur die richtigen Fragen.');
    $alfred_intro_right_strong = $m('alfred_intro_right_strong', 'Dein perfektes Format');
    $alfred_intro_right_sub    = $m('alfred_intro_right_sub',    '3 Schritte · 2 Minuten · 0 Unverbindlichkeiten');
    $alfred_main_h             = $m('alfred_main_h',             'DO YOU FEEL LIKE A HERO?');
    $alfred_main_p             = $m('alfred_main_p',             'Alfred findet heraus, was dich bewegt – und führt dich direkt zu dem Format, das wirklich passt. Nicht der Katalog. Dein Ding.');
    $alfred_step1_t            = $m('alfred_step1_t',            'Worum geht\'s dir gerade?');
    $alfred_step1_s            = $m('alfred_step1_s',            'Dein Anliegen als Ausgangspunkt');
    $alfred_step2_t            = $m('alfred_step2_t',            'Wie willst du arbeiten?');
    $alfred_step2_s            = $m('alfred_step2_s',            'Academy, Inhouse, Coaching oder Tools');
    $alfred_step3_t            = $m('alfred_step3_t',            'Dein perfektes Format');
    $alfred_step3_s            = $m('alfred_step3_s',            'Kuratiert — nicht der ganze Katalog');
    $alfred_opening_msg        = $m('alfred_opening_msg',        'Guten Tag. Ich bin Alfred – kein Held, aber unverzichtbar. Worum geht\'s dir gerade?');
    $alfred_chips_raw          = $m('alfred_chips',              "Ich will besser führen\nMein Team braucht Entwicklung\nWir stehen vor einer Veränderung\nMeetings und Workshops verbessern\nIch will persönlich wachsen");

    $marquee_label     = $m('marquee_label',     'Weniger Bullshit, mehr Impact.');
    $marquee_items_raw = $m('marquee_items',     "ADEG\nBestattung Wien\nBILLA\nInfineon\nBoehringer Ingelheim\nWiener Linien\nAustria Trend Hotels\nRaiffeisen");
    $clients_raw       = $m('clients',           "ADEG\nBestattung Wien\nBILLA\nInfineon\nBoehringer Ingelheim\nWiener Linien\nAustria Trend Hotels\nRaiffeisen");

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
                <textarea name="hp_hero_titel" id="hp_hero_titel" rows="2"><?php echo esc_textarea($hero_titel); ?></textarea>
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
                           value="<?php echo esc_attr($hero_cta_label); ?>" placeholder="Unsere Programme">
                </div>
                <div class="gfp-field">
                    <label for="hp_hero_cta_url">Button-URL</label>
                    <p class="desc">Leer lassen → automatisch zur Kursübersicht</p>
                    <input type="url" name="hp_hero_cta_url" id="hp_hero_cta_url"
                           value="<?php echo esc_attr($hero_cta_url); ?>" placeholder="https://…">
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
                    <input type="text" name="hp_alfred_badge" value="<?php echo esc_attr($alfred_badge); ?>" placeholder="AL">
                </div>
                <div class="gfp-field">
                    <label>Überschrift</label>
                    <input type="text" name="hp_alfred_intro_h" value="<?php echo esc_attr($alfred_intro_h); ?>" placeholder="Meet ALFRED">
                </div>
            </div>
            <div class="gfp-field">
                <label>Intro-Text (links)</label>
                <textarea name="hp_alfred_intro_p" rows="2"><?php echo esc_textarea($alfred_intro_p); ?></textarea>
            </div>
            <div class="gfp-cols-2">
                <div class="gfp-field">
                    <label>Rechte Seite — fetter Text</label>
                    <input type="text" name="hp_alfred_intro_right_strong" value="<?php echo esc_attr($alfred_intro_right_strong); ?>">
                </div>
                <div class="gfp-field">
                    <label>Rechte Seite — kleiner Text</label>
                    <input type="text" name="hp_alfred_intro_right_sub" value="<?php echo esc_attr($alfred_intro_right_sub); ?>">
                </div>
            </div>
            <hr style="margin:20px 0;border-color:#eee;">
            <p style="font-size:13px;font-weight:700;color:#444;margin-bottom:10px;">Hauptbereich (links)</p>
            <div class="gfp-field">
                <label>Headline</label>
                <input type="text" name="hp_alfred_main_h" value="<?php echo esc_attr($alfred_main_h); ?>" placeholder="DO YOU FEEL LIKE A HERO?">
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
                       value="<?php echo esc_attr($marquee_label); ?>" placeholder="Weniger Bullshit, mehr Impact.">
            </div>
            <div class="gfp-field">
                <label for="hp_marquee_items">Laufband-Einträge</label>
                <p class="desc">Ein Eintrag pro Zeile — erscheint endlos scrollend</p>
                <textarea name="hp_marquee_items" id="hp_marquee_items" rows="8"><?php echo esc_textarea($marquee_items_raw); ?></textarea>
            </div>
            <div class="gfp-field">
                <label for="hp_clients">Kunden-Logos / Firmennamen</label>
                <p class="desc">Ein Name pro Zeile — erscheint als Logozeile unter dem Band</p>
                <textarea name="hp_clients" id="hp_clients" rows="8"><?php echo esc_textarea($clients_raw); ?></textarea>
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
                <textarea name="hp_bilder" id="hp_bilder" rows="10" style="font-family:monospace;font-size:12px;"><?php echo esc_textarea($bilder_raw); ?></textarea>
            </div>
            <p style="font-size:12px;color:#999;">Tipp: Lade Bilder zuerst in die WordPress-Mediathek hoch, klicke dort auf ein Bild und kopiere die URL aus „Datei-URL".</p>
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
                           value="<?php echo esc_attr($cta_headline_l1); ?>" placeholder="DO YOU FEEL">
                </div>
                <div class="gfp-field">
                    <label for="hp_cta_headline_l2">Headline — Zeile 2</label>
                    <input type="text" name="hp_cta_headline_l2" id="hp_cta_headline_l2"
                           value="<?php echo esc_attr($cta_headline_l2); ?>" placeholder="LIKE A HERO?">
                </div>
            </div>
            <div class="gfp-field">
                <label for="hp_cta_text">Text unter der Headline</label>
                <textarea name="hp_cta_text" id="hp_cta_text"><?php echo esc_textarea($cta_text); ?></textarea>
            </div>
            <div class="gfp-field">
                <label for="hp_cta_label">Button-Text</label>
                <input type="text" name="hp_cta_label" id="hp_cta_label"
                       value="<?php echo esc_attr($cta_label); ?>" placeholder="Jetzt Programm finden">
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
                       value="<?php echo esc_attr($testi_author); ?>" placeholder="Vorname Nachname · Rolle, Unternehmen">
            </div>
        </div>

        <!-- ── Zahlen ── -->
        <div class="gfp-panel" id="panel-hp-zahlen">
            <div class="gfp-field" style="margin-bottom:24px;">
                <label for="hp_stats_label">Abschnitts-Überschrift</label>
                <input type="text" name="hp_stats_label" id="hp_stats_label"
                       value="<?php echo esc_attr($stats_label); ?>" placeholder="GfP in Zahlen">
            </div>
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
                           value="<?php echo esc_attr($d); ?>" placeholder="Jahre Erfahrung …">
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
            foreach ($fb_fields as $i => $fb) : ?>
            <div class="gfp-fb-card" style="border-left:4px solid <?php echo esc_attr($farben_preview[$fb['farbe']] ?? '#aaa'); ?>;">
                <h4>Karte <?php echo $i; ?> — <span style="color:<?php echo esc_attr($farben_preview[$fb['farbe']] ?? '#aaa'); ?>"><?php echo esc_html($fb['titel']); ?></span></h4>
                <div class="gfp-cols-2">
                    <div class="gfp-field">
                        <label>Nummer</label>
                        <input type="text" name="hp_fb_<?php echo $i; ?>_num" value="<?php echo esc_attr($fb['num']); ?>" placeholder="01">
                    </div>
                    <div class="gfp-field">
                        <label>Tag (kleine Zeile)</label>
                        <input type="text" name="hp_fb_<?php echo $i; ?>_tag" value="<?php echo esc_attr($fb['tag']); ?>" placeholder="Führung & Entwicklung">
                    </div>
                </div>
                <div class="gfp-cols-2">
                    <div class="gfp-field">
                        <label>Titel (große Zeile)</label>
                        <input type="text" name="hp_fb_<?php echo $i; ?>_titel" value="<?php echo esc_attr($fb['titel']); ?>" placeholder="Leadership & Führung">
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
                    <input type="text" name="hp_regel_<?php echo $n; ?>_t" value="<?php echo esc_attr($regel['t']); ?>">
                </div>
                <div class="gfp-field" style="margin:0;">
                    <label style="margin-bottom:3px;">Beschreibung</label>
                    <input type="text" name="hp_regel_<?php echo $n; ?>_b" value="<?php echo esc_attr($regel['b']); ?>">
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
                    <input type="text" name="hp_trainers_h1" id="hp_trainers_h1" value="<?php echo esc_attr($trainers_h1); ?>" placeholder="Unser">
                </div>
                <div class="gfp-field">
                    <label for="hp_trainers_h2">Überschrift Zeile 2</label>
                    <input type="text" name="hp_trainers_h2" id="hp_trainers_h2" value="<?php echo esc_attr($trainers_h2); ?>" placeholder="Team">
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

    document.querySelectorAll('.gfp-hp .gfp-tab').forEach(function(tab) {
        tab.addEventListener('click', function() {
            document.querySelectorAll('.gfp-hp .gfp-tab').forEach(function(t) { t.classList.remove('active'); });
            document.querySelectorAll('.gfp-hp .gfp-panel').forEach(function(p) { p.classList.remove('active'); });
            tab.classList.add('active');
            document.getElementById('panel-' + tab.dataset.panel).classList.add('active');
        });
    });

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

        fetch(ajaxurl, { method: 'POST', credentials: 'same-origin', body: data })
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
