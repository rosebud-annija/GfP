<?php
/**
 * front-page.php
 * GfP — Startseite
 *
 * Wird automatisch als Startseite verwendet (WordPress Template-Hierarchie).
 * Inhalte über ACF-Felder pflegbar (Gruppe: group_homepage).
 * Trainer kommen aus dem CPT "trainer" (optional — Fallback auf Platzhalter).
 * News kommen aus dem CPT "post" (normale WordPress-Beiträge).
 */

defined('ABSPATH') || exit;

// ── Startseiten-Inhalte ────────────────────────────────────────────────────────
// Texte direkt hier anpassen oder über get_post_meta() aus WP-Customizer laden.
// ACF wird auf der Startseite NICHT verwendet (um Konflikte zu vermeiden).

// ── Texte aus WordPress-Admin (Seiten → Startseite → "Startseiten-Inhalte") ──
$hero_eyebrow   = gfp_hp('hero_eyebrow',   'Gesellschaft für Personalentwicklung');
$hero_titel     = gfp_hp('hero_titel',     'SUPERHEROES,<br>LIKE US.');
$hero_sub       = gfp_hp('hero_sub',       'Nicht unbesiegbar. Nur unaufhaltbar.');
$hero_text      = gfp_hp('hero_text',      'Wir entwickeln Menschen, Teams und Organisationen – mit Klarheit, Haltung und Wirkung.');
$hero_cta_label = gfp_hp('hero_cta_label', 'Unsere Programme');
$hero_cta_url   = gfp_hp('hero_cta_url',  '') ?: (get_post_type_archive_link('kurs') ?: '/kurse/');

$problem_text   = gfp_hp('problem_text',   'Die Welt ist komplex. Organisationen auch. Wir helfen euch, den Überblick zu behalten – und wirksam zu handeln. Mit Erfahrung, Methode und echter Leidenschaft.');

$cta_text       = gfp_hp('cta_text',       'Dann lass uns gemeinsam herausfinden, welches GfP-Programm zu euch passt.');
$cta_label      = gfp_hp('cta_label',      'Jetzt Programm finden');
$cta_url        = get_post_type_archive_link('kurs') ?: '/kurse/';

$testi_quote    = gfp_hp('testi_quote',    '„GfP hat uns nicht nur trainiert – sie haben uns gezeigt, was wirklich möglich ist."');
$testi_author   = gfp_hp('testi_author',   'Anna Müller · Head of People, TechCorp GmbH');

// Trainer laden (CPT "trainer" oder Fallback)
$trainer_posts = get_posts(['post_type' => 'trainer', 'posts_per_page' => 6, 'post_status' => 'publish']);
$trainer_fallback = [
    ['name' => 'Maria Berger',   'rolle' => 'Leadership Coach',        'tags' => ['Führung','Coaching'],     'bio' => 'Maria begleitet Führungskräfte seit 15 Jahren dabei, authentisch und wirksam zu führen.'],
    ['name' => 'Thomas Klein',   'rolle' => 'Facilitation Expert',     'tags' => ['Moderation','Teams'],     'bio' => 'Thomas macht komplexe Workshops zu klaren Ergebnissen – mit Leichtigkeit und Methode.'],
    ['name' => 'Sarah Hofmann',  'rolle' => 'Organisationsberaterin',  'tags' => ['Transformation','Agile'], 'bio' => 'Sarah navigiert Organisationen durch Wandel – mit Klarheit, Empathie und Pragmatismus.'],
    ['name' => 'Jan Schreiber',  'rolle' => 'Team Coach',              'tags' => ['Teams','Konflikte'],      'bio' => 'Jan bringt Teams zusammen, die auseinanderdriften – und stärkt, was schon funktioniert.'],
    ['name' => 'Lisa Werner',    'rolle' => 'Persönlichkeitstrainerin', 'tags' => ['Skills','Kommunikation'],'bio' => 'Lisa entwickelt Menschen, die sich selbst kennen und andere mitreißen.'],
    ['name' => 'Peter Bauer',    'rolle' => 'Change Consultant',       'tags' => ['Strategie','OE'],         'bio' => 'Peter denkt Veränderung systemisch – und begleitet sie mit Konsequenz.'],
];

// News (letzte 4 Beiträge)
$news = get_posts(['post_type' => 'post', 'posts_per_page' => 4, 'post_status' => 'publish']);

// Fachbereiche für die Karten-Sektion
$fb_farben_default = ['blue', 'teal', 'orange', 'purple', 'sky'];
$fb_defaults_fp = [
    1 => ['01', 'Führung & Entwicklung', 'Leadership & Führung',           'Führung, die wirkt. Nicht durch Kontrolle, sondern durch Klarheit und Haltung.'],
    2 => ['02', 'Workshopdesign',         'Facilitation & Moderation',      'Meetings, die Energie geben statt nehmen. Workshops mit echten Ergebnissen.'],
    3 => ['03', 'Teamentwicklung',         'Teams & Collaboration',          'Teams, die mehr sind als die Summe ihrer Teile. Zusammenarbeit, die trägt.'],
    4 => ['04', 'Wandel gestalten',        'Organisation & Transformation',  'Veränderung als Chance. Strukturen, die sich anpassen. Menschen, die mitgehen.'],
    5 => ['05', 'Persönlichkeit',          'Personality & Skills',           'Kompetenzen, die bleiben. Selbstwirksamkeit, die trägt.'],
];
$kurse_archiv_url = get_post_type_archive_link('kurs') ?: '/kurse/';
$fachbereiche_hp = [];
foreach ($fb_defaults_fp as $i => [$d_num, $d_tag, $d_titel, $d_text]) {
    $titel = gfp_hp("fb_{$i}_titel", $d_titel);
    $term  = get_term_by('name', $titel, 'fachbereich');

    // Farbe: admin-Einstellung → taxonomy-Term-Meta → Standardwert nach Position
    $farbe_slug = gfp_hp("fb_{$i}_farbe", '');
    if (!$farbe_slug) {
        $farbe_slug = $term
            ? (get_term_meta($term->term_id, 'farbe', true) ?: $fb_farben_default[$i - 1])
            : $fb_farben_default[$i - 1];
    }
    $farbe = gfp_farbe_hex($farbe_slug);
    $link  = $kurse_archiv_url . '#' . rawurlencode(mb_strtolower($titel, 'UTF-8'));
    $fachbereiche_hp[] = [
        'num'   => gfp_hp("fb_{$i}_num",   $d_num),
        'tag'   => gfp_hp("fb_{$i}_tag",   $d_tag),
        'titel' => $titel,
        'text'  => gfp_hp("fb_{$i}_text",  $d_text),
        'farbe' => $farbe,
        'link'  => $link,
    ];
}

// Manifest-Regeln
$regel_defaults_t = ['Klarheit vor Komplexität','Haltung zeigen','Wirkung statt Wellness','Menschen ernst nehmen','Weniger ist mehr','Systeme denken','Humor erlaubt','Superhero-Mindset'];
$regel_defaults_b = ['Wir vereinfachen, ohne zu verflachen.','Wir stehen für etwas. Auch wenn es unbequem ist.','Entwicklung, die nachhallt. Nicht nur im Seminarraum.','Kein Babysitting. Echte Auseinandersetzung.','Lieber ein Thema wirklich durchdringen als vieles streifen.','Individuen sind Teil von Systemen. Wir vergessen das nie.','Ernst nehmen ≠ humorlos sein. Lachen öffnet Türen.','Nicht unbesiegbar. Nur unaufhaltbar. Wie wir alle.'];
$regeln = [];
for ($n = 1; $n <= 8; $n++) {
    $regeln[] = [
        'n' => sprintf('%02d', $n),
        't' => gfp_hp("regel_{$n}_t", $regel_defaults_t[$n - 1]),
        'b' => gfp_hp("regel_{$n}_b", $regel_defaults_b[$n - 1]),
    ];
}

// Trainer-Sektion
$trainers_h1  = gfp_hp('trainers_h1',  'Unser');
$trainers_h2  = gfp_hp('trainers_h2',  'Team');
$trainers_sub = gfp_hp('trainers_sub', 'Erfahren, direkt, mit Haltung. Unsere Trainer bringen mit, was wirklich zählt.');

// CTA-Sektion
$cta_headline_l1 = gfp_hp('cta_headline_l1', 'DO YOU FEEL');
$cta_headline_l2 = gfp_hp('cta_headline_l2', 'LIKE A HERO?');

// Zahlen-Sektion
$stats_label = gfp_hp('stats_label', 'GfP in Zahlen');

// Alfred
$alfred_badge              = gfp_hp('alfred_badge',              'AL');
$alfred_intro_h            = gfp_hp('alfred_intro_h',            'Meet ALFRED');
$alfred_intro_p            = gfp_hp('alfred_intro_p',            'Dein persönlicher Guide zu deinem GfP-Format. Kein Cape, kein Bullshit — nur die richtigen Fragen.');
$alfred_intro_right_strong = gfp_hp('alfred_intro_right_strong', 'Dein perfektes Format');
$alfred_intro_right_sub    = gfp_hp('alfred_intro_right_sub',    '3 Schritte · 2 Minuten · 0 Unverbindlichkeiten');
$alfred_main_h             = gfp_hp('alfred_main_h',             'DO YOU FEEL LIKE A HERO?');
$alfred_main_p             = gfp_hp('alfred_main_p',             'Alfred findet heraus, was dich bewegt – und führt dich direkt zu dem Format, das wirklich passt. Nicht der Katalog. Dein Ding.');
$alfred_step1_t            = gfp_hp('alfred_step1_t',            'Worum geht\'s dir gerade?');
$alfred_step1_s            = gfp_hp('alfred_step1_s',            'Dein Anliegen als Ausgangspunkt');
$alfred_step2_t            = gfp_hp('alfred_step2_t',            'Wie willst du arbeiten?');
$alfred_step2_s            = gfp_hp('alfred_step2_s',            'Academy, Inhouse, Coaching oder Tools');
$alfred_step3_t            = gfp_hp('alfred_step3_t',            'Dein perfektes Format');
$alfred_step3_s            = gfp_hp('alfred_step3_s',            'Kuratiert — nicht der ganze Katalog');
$alfred_opening_msg        = gfp_hp('alfred_opening_msg',        'Guten Tag. Ich bin Alfred – kein Held, aber unverzichtbar. Worum geht\'s dir gerade?');
$alfred_chips_raw          = gfp_hp('alfred_chips',              "Ich will besser führen\nMein Team braucht Entwicklung\nWir stehen vor einer Veränderung\nMeetings und Workshops verbessern\nIch will persönlich wachsen");
$alfred_chips              = array_filter(array_map('trim', explode("\n", $alfred_chips_raw)));

// Marquee
$marquee_label     = gfp_hp('marquee_label',  'Weniger Bullshit, mehr Impact.');
$marquee_items_raw = gfp_hp('marquee_items',  "ADEG\nBestattung Wien\nBILLA\nInfineon\nBoehringer Ingelheim\nWiener Linien\nAustria Trend Hotels\nRaiffeisen");
$marquee_items     = array_filter(array_map('trim', explode("\n", $marquee_items_raw)));
$clients_raw       = gfp_hp('clients',        "ADEG\nBestattung Wien\nBILLA\nInfineon\nBoehringer Ingelheim\nWiener Linien\nAustria Trend Hotels\nRaiffeisen");
$clients           = array_filter(array_map('trim', explode("\n", $clients_raw)));

// Bildstreifen (bis zu 8 Bilder, format: URL|Caption pro Zeile)
$bilder_raw = gfp_hp('bilder', '');
$bilder = [];
if ($bilder_raw) {
    foreach (array_filter(array_map('trim', explode("\n", $bilder_raw))) as $line) {
        $parts = explode('|', $line, 2);
        $bilder[] = ['url' => trim($parts[0]), 'caption' => trim($parts[1] ?? '')];
    }
}

// Manifest Intro
$manifest_intro = gfp_hp('manifest_intro', 'Was uns antreibt. Was uns ausmacht. Was wir glauben, wenn es um Entwicklung von Menschen und Organisationen geht.');


get_header();
?>

<!-- ══ 1. HERO ════════════════════════════════════════════════════════════════ -->
<section id="hero">
    <div class="hero-content">
        <p class="hero-eyebrow"><?php echo esc_html($hero_eyebrow); ?></p>
        <h1 class="hero-title display"><?php echo nl2br(esc_html($hero_titel)); ?></h1>
        <p class="hero-sub"><?php echo nl2br(esc_html($hero_sub ?: 'Nicht unbesiegbar. Nur unaufhaltbar.')); ?></p>
        <p class="hero-body"><?php echo nl2br(esc_html($hero_text)); ?></p>
        <div class="hero-ctas">
            <a href="<?php echo esc_url($hero_cta_url ?: get_post_type_archive_link('kurs')); ?>" class="btn-dark">
                <?php echo esc_html($hero_cta_label ?: 'Unsere Programme'); ?> →
            </a>
            <a href="#alfred" class="btn-outline">Alfred fragen</a>
        </div>
    </div>
</section>

<!-- ══ 2. ALFRED ══════════════════════════════════════════════════════════════ -->
<section id="alfred">
    <div class="alfred-intro">
        <div class="alfred-intro-left">
            <div class="alfred-badge"><span class="alfred-badge-inner"><?php echo esc_html($alfred_badge); ?></span></div>
            <div class="alfred-intro-text">
                <h3><?php echo esc_html($alfred_intro_h); ?></h3>
                <p><?php echo esc_html($alfred_intro_p); ?></p>
            </div>
        </div>
        <div class="alfred-intro-right">
            <strong><?php echo esc_html($alfred_intro_right_strong); ?></strong>
            <?php echo esc_html($alfred_intro_right_sub); ?>
        </div>
    </div>

    <div class="alfred-main">
        <div class="alfred-left">
            <h2 class="display"><?php echo esc_html($alfred_main_h); ?></h2>
            <p><?php echo esc_html($alfred_main_p); ?></p>
            <div class="alfred-steps">
                <div class="alfred-step active" id="as1">
                    <div class="alfred-step-dot">1</div>
                    <div class="alfred-step-label">
                        <strong><?php echo esc_html($alfred_step1_t); ?></strong>
                        <span><?php echo esc_html($alfred_step1_s); ?></span>
                    </div>
                </div>
                <div class="alfred-step" id="as2">
                    <div class="alfred-step-dot">2</div>
                    <div class="alfred-step-label">
                        <strong><?php echo esc_html($alfred_step2_t); ?></strong>
                        <span><?php echo esc_html($alfred_step2_s); ?></span>
                    </div>
                </div>
                <div class="alfred-step" id="as3">
                    <div class="alfred-step-dot">3</div>
                    <div class="alfred-step-label">
                        <strong><?php echo esc_html($alfred_step3_t); ?></strong>
                        <span><?php echo esc_html($alfred_step3_s); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="alfred-right">
            <div class="chat-topbar">
                <div class="chat-topbar-left">
                    <div class="chat-av"><?php echo esc_html($alfred_badge); ?></div>
                    <div>
                        <div class="chat-name">Alfred</div>
                        <div class="chat-status">Bereit</div>
                    </div>
                </div>
                <div class="chat-topbar-meta">by GfP</div>
            </div>

            <div class="chat-msgs" id="chatMsgs"></div>

            <div class="chat-opts" id="chatOpts"></div>

            <div class="chat-input-row">
                <input type="text" class="cinput" id="chatInput" placeholder="Oder schreib direkt zu Alfred…">
                <button class="creset" id="chatReset" title="Neu starten">↺</button>
                <button class="csend" id="chatSend" title="Senden">→</button>
            </div>
        </div>
    </div>
</section>

<!-- ══ 3. PROBLEM ════════════════════════════════════════════════════════════ -->
<section id="problem" class="fu">
    <div class="problem-inner">
        <div class="problem-headline-row">
            <h2 class="problem-headline display">
                Wir schaffen <span class="hl lime">Klarheit</span><br>im <span class="hl blue">Chaos</span>
            </h2>
            <p class="problem-body-text">
                <?php echo esc_html($problem_text); ?>
            </p>
        </div>
        <div class="problem-cta-row">
            <a href="<?php echo esc_url(get_post_type_archive_link('kurs')); ?>" class="btn-lime">
                Alle Programme ansehen →
            </a>
        </div>
    </div>
</section>

<!-- ══ 4. MARQUEE ════════════════════════════════════════════════════════════ -->
<div id="marquee">
    <p class="marquee-label"><?php echo esc_html($marquee_label); ?></p>
    <div class="marquee-track">
        <?php
        for ($i = 0; $i < 2; $i++) :
            foreach ($marquee_items as $item) : ?>
                <span class="marquee-item">
                    <?php echo esc_html($item); ?> <span class="sep">★</span>
                </span>
            <?php endforeach;
        endfor; ?>
    </div>
</div>

<div id="clients">
    <div class="clients-inner">
        <?php foreach ($clients as $logo) : ?>
            <span class="clogo"><?php echo esc_html($logo); ?></span>
        <?php endforeach; ?>
    </div>
</div>

<!-- ══ 5. FACHBEREICHE ═══════════════════════════════════════════════════════ -->
<section id="fachbereiche" class="fu">
    <div class="fb-header">
        <h2 class="display">Unsere<br>Fachbereiche</h2>
        <p>Fünf Themenfelder. Eine Haltung: Entwicklung, die wirkt.</p>
    </div>
    <div class="fb-grid">
        <?php foreach ($fachbereiche_hp as $fb) : ?>
            <a href="<?php echo esc_url($fb['link']); ?>"
               class="fb-card"
               style="text-decoration:none; --fb-farbe:<?php echo esc_attr($fb['farbe']); ?>;">
                <div class="fb-card-num"><?php echo esc_html($fb['num']); ?></div>
                <div class="fb-card-tag"><?php echo esc_html($fb['tag']); ?></div>
                <div class="fb-card-title" style="color:<?php echo esc_attr($fb['farbe']); ?>;">
                    <?php echo esc_html($fb['titel']); ?>
                </div>
                <div class="fb-card-body"><?php echo esc_html($fb['text']); ?></div>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<!-- ══ 5b. BILDSTREIFEN ═══════════════════════════════════════════════════════ -->
<?php if (!empty($bilder)) : ?>
<section id="image-hero">
    <div class="image-hero-track" id="ihTrack">
        <?php
        for ($pass = 0; $pass < 2; $pass++) :
            foreach ($bilder as $bild) :
        ?>
            <div class="ih-item">
                <img src="<?php echo esc_url($bild['url']); ?>" alt="<?php echo esc_attr($bild['caption']); ?>" loading="lazy">
                <?php if ($bild['caption']) : ?>
                    <div class="ih-caption"><?php echo esc_html($bild['caption']); ?></div>
                <?php endif; ?>
            </div>
        <?php
            endforeach;
        endfor;
        ?>
    </div>
</section>
<?php endif; ?>

<!-- ══ 6. TRAINER ════════════════════════════════════════════════════════════ -->
<section id="trainers" class="fu">
    <div class="trainers-header">
        <h2 class="display"><?php echo esc_html($trainers_h1); ?><br><?php echo esc_html($trainers_h2); ?></h2>
        <p><?php echo esc_html($trainers_sub); ?></p>
    </div>
    <div class="trainer-grid">
        <?php if (!empty($trainer_posts)) :
            foreach ($trainer_posts as $i => $t) :
                setup_postdata($t);
                $rolle    = get_post_meta($t->ID, '_trainer_role', true) ?: '';
                $bio      = get_post_meta($t->ID, '_trainer_bio',  true) ?: get_the_excerpt($t);
                $tags_raw = get_post_meta($t->ID, '_trainer_tags', true) ?: '';
                $tags     = $tags_raw ? array_map('trim', explode(',', $tags_raw)) : [];
                $foto  = get_the_post_thumbnail_url($t->ID, 'medium');
            ?>
                <div class="trainer-card">
                    <div class="trainer-img-wrap">
                        <?php if ($foto) : ?>
                            <img src="<?php echo esc_url($foto); ?>" alt="<?php echo esc_attr(get_the_title($t)); ?>">
                        <?php else : ?>
                            <div class="trainer-img-placeholder"><?php echo esc_html(mb_substr(get_the_title($t), 0, 1)); ?></div>
                        <?php endif; ?>
                        <div class="trainer-overlay">
                            <div class="trainer-bio">
                                <strong><?php echo esc_html(get_the_title($t)); ?></strong>
                                <?php echo esc_html($bio); ?>
                            </div>
                        </div>
                    </div>
                    <div class="trainer-info">
                        <div class="trainer-name"><?php echo esc_html(get_the_title($t)); ?></div>
                        <div class="trainer-role"><?php echo esc_html($rolle); ?></div>
                        <div class="trainer-tags">
                            <?php if (is_array($tags)) foreach ($tags as $tag) : ?>
                                <span class="trainer-tag"><?php echo esc_html($tag); ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach;
            wp_reset_postdata();
        else :
            foreach ($trainer_fallback as $i => $t) : ?>
                <div class="trainer-card">
                    <div class="trainer-img-wrap">
                        <div class="trainer-img-placeholder"><?php echo esc_html(mb_substr($t['name'], 0, 1)); ?></div>
                        <div class="trainer-overlay">
                            <div class="trainer-bio">
                                <strong><?php echo esc_html($t['name']); ?></strong>
                                <?php echo esc_html($t['bio']); ?>
                            </div>
                        </div>
                    </div>
                    <div class="trainer-info">
                        <div class="trainer-name"><?php echo esc_html($t['name']); ?></div>
                        <div class="trainer-role"><?php echo esc_html($t['rolle']); ?></div>
                        <div class="trainer-tags">
                            <?php foreach ($t['tags'] as $tag) : ?>
                                <span class="trainer-tag"><?php echo esc_html($tag); ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach;
        endif; ?>
    </div>
</section>

<!-- ══ 7. STATS ══════════════════════════════════════════════════════════════ -->
<section id="stats" class="fu">
    <p class="stats-label"><?php echo esc_html($stats_label); ?></p>
    <div class="stats-grid">
        <div class="stat">
            <div class="stat-n"><?php echo esc_html(gfp_hp('stat_1_n', '30+')); ?></div>
            <div class="stat-d"><?php echo esc_html(gfp_hp('stat_1_d', 'Jahre Erfahrung in Personalentwicklung und Training')); ?></div>
        </div>
        <div class="stat">
            <div class="stat-n"><?php echo esc_html(gfp_hp('stat_2_n', '5.000+')); ?></div>
            <div class="stat-d"><?php echo esc_html(gfp_hp('stat_2_d', 'Teilnehmende pro Jahr in Academy und Inhouse-Programmen')); ?></div>
        </div>
        <div class="stat">
            <div class="stat-n"><?php echo esc_html(gfp_hp('stat_3_n', '98%')); ?></div>
            <div class="stat-d"><?php echo esc_html(gfp_hp('stat_3_d', 'Weiterempfehlungsrate unserer Teilnehmenden')); ?></div>
        </div>
    </div>
</section>

<!-- ══ 8. CTA ════════════════════════════════════════════════════════════════ -->
<section id="cta-hero" class="fu">
    <div class="cta-hero-inner">
        <h2 class="display"><?php echo esc_html($cta_headline_l1); ?><br><?php echo esc_html($cta_headline_l2); ?></h2>
        <p><?php echo esc_html($cta_text); ?></p>
        <a href="<?php echo esc_url($cta_url); ?>" class="btn-sky">
            <?php echo esc_html($cta_label); ?> →
        </a>
    </div>
</section>

<!-- ══ 9. TESTIMONIAL ════════════════════════════════════════════════════════ -->
<section id="testimonial" class="fu">
    <div class="testi-inner">
        <span class="tag-label">Was andere sagen</span>
        <blockquote><?php echo esc_html($testi_quote); ?></blockquote>
        <p class="testi-author"><?php echo esc_html($testi_author); ?></p>
    </div>
</section>

<!-- ══ 10. MANIFEST ══════════════════════════════════════════════════════════ -->
<section id="manifest" class="fu">
    <div class="manifest-inner">
        <div class="manifest-top">
            <h2 class="display">Unser<br><em>Manifest</em></h2>
            <p><?php echo esc_html($manifest_intro); ?></p>
        </div>
        <div class="rules">
            <?php foreach ($regeln as $r) : ?>
                <div class="rule">
                    <div class="rule-n"><?php echo esc_html($r['n']); ?></div>
                    <div class="rule-t"><?php echo esc_html($r['t']); ?></div>
                    <div class="rule-b"><?php echo esc_html($r['b']); ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ══ 11. NEWS ══════════════════════════════════════════════════════════════ -->
<section id="news" class="fu">
    <div class="news-inner">
        <div class="news-header">
            <h2 class="display">Aktuelles</h2>
            <a href="<?php echo esc_url(get_permalink(get_option('page_for_posts'))); ?>">Alle Beiträge →</a>
        </div>
        <div class="news-grid">
            <?php if (!empty($news)) :
                foreach ($news as $post) :
                    setup_postdata($post);
                    $kategorie = get_the_category($post->ID);
                    $kat_name  = !empty($kategorie) ? $kategorie[0]->name : 'News';
                ?>
                    <a href="<?php the_permalink($post); ?>" class="news-card" style="text-decoration:none;color:inherit;">
                        <div class="ncc"></div>
                        <div class="news-card-tag"><?php echo esc_html($kat_name); ?></div>
                        <h3><?php echo esc_html(get_the_title($post)); ?></h3>
                        <p><?php echo esc_html(wp_trim_words(get_the_excerpt($post), 18)); ?></p>
                    </a>
                <?php endforeach;
                wp_reset_postdata();
            else : ?>
                <p style="grid-column:1/-1;color:rgba(0,0,0,.4);padding:2rem 0;">Noch keine Beiträge vorhanden.</p>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- ══ SCROLL-FADE & ALFRED-CHAT JS ══════════════════════════════════════════ -->
<?php
$_alfred_kurse = [];
$_kurs_posts   = get_posts(['post_type' => 'kurs', 'posts_per_page' => -1, 'post_status' => 'publish', 'orderby' => 'title', 'order' => 'ASC']);
foreach ($_kurs_posts as $_k) {
    $_fb      = gfp_get_fachbereich($_k->ID);
    $_fb_name = $_fb ? $_fb->name : '';
    $_fb_norm = $_fb_name ? mb_strtolower(html_entity_decode($_fb_name, ENT_QUOTES | ENT_HTML5, 'UTF-8'), 'UTF-8') : '';
    $_farbe   = gfp_farbe_hex(gfp_get_kurs_farbe($_k->ID)) ?: '#27348b';
    $_alfred_kurse[] = [
        'title'   => $_k->post_title,
        'format'  => get_post_meta($_k->ID, '_kurs_format', true) ?: '',
        'fb'      => $_fb_name,
        'fb_norm' => $_fb_norm,
        'farbe'   => $_farbe,
        'excerpt' => wp_strip_all_tags(get_the_excerpt($_k)),
        'url'     => get_permalink($_k->ID),
    ];
}
unset($_kurs_posts, $_k, $_fb, $_fb_name, $_fb_norm, $_farbe);
?>
<script>
const fuEls = document.querySelectorAll('.fu');
const obs = new IntersectionObserver(entries => {
    entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('vis'); obs.unobserve(e.target); } });
}, { threshold: 0.1 });
fuEls.forEach(el => obs.observe(el));

const ALFRED_BADGE = <?php echo json_encode($alfred_badge, JSON_UNESCAPED_UNICODE); ?>;
const OPENING_MSG  = <?php echo json_encode($alfred_opening_msg, JSON_UNESCAPED_UNICODE); ?>;
const CHIPS        = <?php echo json_encode(array_values($alfred_chips), JSON_UNESCAPED_UNICODE); ?>;
const KURSE        = <?php echo json_encode($_alfred_kurse, JSON_UNESCAPED_UNICODE); ?>;
const KURSE_URL    = <?php echo json_encode(get_post_type_archive_link('kurs') ?: '/kurse/', JSON_UNESCAPED_UNICODE); ?>;

// Fachbereich-Farbzuordnung — nur für Ergebniskarten, kein Auswahlschritt
const FB_COLOR = {
    'leadership & führung':          '#0073BC',
    'facilitation & moderation':     '#23B0A5',
    'teams & collaboration':         '#F18712',
    'organisation & transformation': '#9E4493',
    'personality & skills':          '#50C1E0',
};
const FB_DISPLAY = {
    'leadership & führung':          'Leadership & Führung',
    'facilitation & moderation':     'Facilitation & Moderation',
    'teams & collaboration':         'Teams & Collaboration',
    'organisation & transformation': 'Organisation & Transformation',
    'personality & skills':          'Personality & Skills',
};

// Keywords pro Fachbereich — für Soft-Scoring (keine harte Filterung)
const FB_KEYWORDS = {
    'leadership & führung':          ['führ','leadership','leitung','chef','vorgesetzt','entscheid','delegier','motivat','management'],
    'facilitation & moderation':     ['meeting','workshop','moderat','facilit','sitzung','besprechung','seminar','storytell','präsent'],
    'teams & collaboration':         ['team','zusammenarbeit','gruppe','kooperat','homeoffice','remote','kolleg'],
    'organisation & transformation': ['veränder','transformation','change','organis','wandel','innovation','agil','learning','recruiting','hr','pe'],
    'personality & skills':          ['persönlich','skill','kompetenz','kommunikation','auftreten','rhetorik','stress','resilienz','burnout','zeitmanag','selbst'],
};

// Kontext-Keywords — boosten bestimmte Kurstypen in den Ergebnissen
const CONTEXT_BOOST = {
    akut:        ['kompakt', 'online', 'toolbox', 'werkstatt', 'praxis', 'intensiv', 'tools'],
    langfristig: ['programm', 'qualifizierung', 'parcours', 'expedition', 'zertifikat', 'lerngang', 'lern'],
    projekt:     ['best practice', 'action learning', 'challenge', 'innovation', 'startup', 'strateg', 'business model'],
};

const msgs  = document.getElementById('chatMsgs');
const opts  = document.getElementById('chatOpts');
const input = document.getElementById('chatInput');
const send  = document.getElementById('chatSend');
const reset = document.getElementById('chatReset');

let step     = 0;
let _topic   = '';
let _format  = '';
let _context = '';

function syncSteps() {
    const s = [document.getElementById('as1'), document.getElementById('as2'), document.getElementById('as3')];
    if (!s[0]) return;
    s.forEach(el => el.classList.remove('active', 'done'));
    if      (step === 0) { s[0].classList.add('active'); }
    else if (step === 1) { s[0].classList.add('done'); s[1].classList.add('active'); }
    else if (step === 2) { s[0].classList.add('done'); s[1].classList.add('done'); s[2].classList.add('active'); }
    else                 { s[0].classList.add('done'); s[1].classList.add('done'); s[2].classList.add('done'); }
}

function addMsg(html, type) {
    const d = document.createElement('div');
    d.className = 'msg ' + type;
    d.innerHTML = '<div class="av">' + (type === 'b' ? ALFRED_BADGE : 'Du') + '</div><div class="bbl">' + html + '</div>';
    msgs.appendChild(d);
    msgs.scrollTop = msgs.scrollHeight;
}

function typing(cb) {
    const d = document.createElement('div');
    d.className = 'msg b'; d.id = 'typing';
    d.innerHTML = '<div class="av">' + ALFRED_BADGE + '</div><div class="bbl"><div class="typing"><span></span><span></span><span></span></div></div>';
    msgs.appendChild(d);
    msgs.scrollTop = msgs.scrollHeight;
    setTimeout(() => { d.remove(); cb(); }, 900);
}

function showChips(list, handler) {
    opts.innerHTML = '';
    list.forEach(label => {
        const btn = document.createElement('button');
        btn.className = 'copt';
        btn.textContent = label;
        btn.onclick = () => (handler || handleInput)(label);
        opts.appendChild(btn);
    });
}

// Stem-Match: direkter Treffer oder Stammform (~70% der Zeichen, mind. 4)
function stemMatch(word, text) {
    if (word.length < 3) return 0;
    if (text.includes(word)) return 6;
    const stem = word.slice(0, Math.max(4, Math.round(word.length * 0.7)));
    return (stem.length >= 4 && text.includes(stem)) ? 3 : 0;
}

function scoreKurs(k, topic, contextKey) {
    const t          = topic.toLowerCase();
    const titleLow   = k.title.toLowerCase();
    const excerptLow = (k.excerpt || '').toLowerCase();
    let s = 0;

    // Fachbereich-Soft-Boost: Anliegen passt thematisch zum Fachbereich des Kurses
    (FB_KEYWORDS[k.fb_norm] || []).forEach(kw => { if (t.includes(kw)) s += 4; });

    // Wort-für-Wort im Kurstitel (Stamm-basiert, 3× Gewicht) und Excerpt
    const words = t.replace(/[^\wäöüß\s]/g, ' ').trim().split(/\s+/).filter(w => w.length > 2);
    words.forEach(w => {
        s += stemMatch(w, titleLow) * 3;
        s += stemMatch(w, excerptLow);
    });

    // Kontext-Boost: passende Kurstypen nach Dringlichkeit/Rahmen
    (CONTEXT_BOOST[contextKey] || []).forEach(kw => { if (titleLow.includes(kw)) s += 5; });

    return s;
}

function fmtKey(val) {
    const v = val.toLowerCase();
    if (v.includes('academy'))    return 'academy';
    if (v.includes('inhouse'))    return 'inhouse';
    if (v.includes('coaching'))   return 'coaching';
    if (v.includes('consulting')) return 'consulting';
    return '';
}

function ctxKey(val) {
    const v = val.toLowerCase();
    if (v.includes('akut') || v.includes('bald') || v.includes('schnell')) return 'akut';
    if (v.includes('langfristig') || v.includes('programm'))               return 'langfristig';
    if (v.includes('projekt'))                                              return 'projekt';
    return '';
}

function showRecommendations() {
    const fmt = fmtKey(_format);
    const ctx = ctxKey(_context);

    // Format-Filter mit Fallback: zu wenig Treffer → alle Formate zulassen
    let pool = fmt ? KURSE.filter(k => k.format === fmt) : KURSE;
    if (pool.length < 5) pool = KURSE;

    const scored = pool
        .map(k => ({ ...k, _s: scoreKurs(k, _topic, ctx) }))
        .sort((a, b) => b._s - a._s || Math.random() - 0.5);
    const top = scored.slice(0, 3);

    const cards = top.map(k => {
        const color      = k.farbe || FB_COLOR[k.fb_norm] || '#27348b';
        const fbLabel    = FB_DISPLAY[k.fb_norm] || '';
        const fmtLabel   = ({ academy: 'Academy', inhouse: 'Inhouse', coaching: 'Coaching', consulting: 'Consulting' })[k.format] || '';
        const tagText    = [fbLabel, fmtLabel].filter(Boolean).join(' · ');
        const desc       = k.excerpt ? k.excerpt.substring(0, 100).trimEnd() + '…' : '';
        return '<a href="' + k.url + '" class="alfred-kurs-card" style="--akc-color:' + color + '" target="_blank" rel="noopener">'
            + (tagText ? '<span class="akc-fb" style="color:' + color + '">' + tagText + '</span>' : '')
            + '<strong class="akc-title">' + k.title + '</strong>'
            + (desc ? '<span class="akc-desc">' + desc + '</span>' : '')
            + '</a>';
    }).join('');

    const archiveUrl = KURSE_URL;
    const allLink    = '<a href="' + archiveUrl + '" class="akc-all-link">Alle 120 Programme ansehen →</a>';
    addMsg('Hier sind drei passende Programme:<br><br>' + cards + allLink, 'b');
    input.disabled = true;
    send.disabled  = true;
}

function handleInput(val) {
    if (!val.trim() || step >= 3) return;
    addMsg(val, 'u');
    opts.innerHTML = '';
    input.value = '';
    step++;
    syncSteps();

    typing(() => {
        if (step === 1) {
            _topic = val;
            addMsg('Wie möchtest du das angehen?', 'b');
            showChips(['Coaching — 1:1 begleitet', 'Academy — offenes Seminar', 'Inhouse — bei uns im Unternehmen']);
        } else if (step === 2) {
            _format = val;
            addMsg('Eine Frage noch: Was ist dein Rahmen?', 'b');
            showChips([
                'Akuter Bedarf — möglichst bald',
                'Langfristige Entwicklung — wir denken in Programmen',
                'Konkretes Projekt läuft gerade',
            ]);
        } else {
            _context = val;
            showRecommendations();
        }
    });
}

function resetChat() {
    msgs.innerHTML = '';
    opts.innerHTML = '';
    input.disabled = false;
    send.disabled  = false;
    step           = 0;
    _topic         = '';
    _format        = '';
    _context       = '';
    syncSteps();
    addMsg(OPENING_MSG, 'b');
    showChips(CHIPS);
}

send.addEventListener('click', () => handleInput(input.value));
if (reset) reset.addEventListener('click', resetChat);
input.addEventListener('keydown', e => { if (e.key === 'Enter') handleInput(input.value); });

resetChat();
</script>

<?php get_footer(); ?>
