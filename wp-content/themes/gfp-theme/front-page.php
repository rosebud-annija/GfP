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
$trainer_posts = get_posts([
    'post_type'      => 'trainer',
    'posts_per_page' => 6,
    'post_status'    => 'publish',
    'orderby'        => ['menu_order' => 'ASC', 'title' => 'ASC'],
    'meta_query'     => [['key' => '_trainer_startseite', 'value' => '1']],
]);
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

// ── Schema.org: Organization + LocalBusiness ──────────────────────────────────
$schema_email    = gfp_hp('ft_email', '');
$schema_linkedin = gfp_hp('ft_linkedin', '');
$schema_sameAs   = array_values(array_filter([
    $schema_linkedin,
    gfp_hp('ft_instagram', ''),
    gfp_hp('ft_youtube', ''),
], fn($v) => $v && $v !== '#'));
$schema_org = [
    '@context' => 'https://schema.org',
    '@graph'   => [
        [
            '@type'       => ['Organization', 'LocalBusiness'],
            '@id'         => home_url('/#organization'),
            'name'        => 'GfP – Gesellschaft für Personalentwicklung',
            'url'         => home_url('/'),
            'logo'        => [
                '@type' => 'ImageObject',
                'url'   => get_theme_file_uri('assets/img/logo.png'),
            ],
            'description' => 'Weiterbildungsorganisation für Leadership, Facilitation, Teamentwicklung und Organisationsentwicklung in Österreich.',
            'address'     => [
                '@type'           => 'PostalAddress',
                'addressCountry'  => 'AT',
                'addressLocality' => 'Wien',
            ],
            'email'       => $schema_email ?: null,
            'sameAs'      => $schema_sameAs,
        ],
    ],
];
echo '<script type="application/ld+json">' . wp_json_encode($schema_org, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>' . "\n";
?>

<!-- ══ 1. HERO ════════════════════════════════════════════════════════════════ -->
<section id="hero">
    <div class="hero-rail">
        <div class="hero-content">
            <p class="hero-eyebrow"><?php echo esc_html($hero_eyebrow); ?></p>
            <h1 class="hero-title display"><?php echo nl2br(esc_html($hero_titel)); ?></h1>
            <p class="hero-sub"><?php echo nl2br(esc_html($hero_sub ?: 'Nicht unbesiegbar. Nur unaufhaltbar.')); ?></p>
            <p class="hero-body"><?php echo nl2br(esc_html($hero_text)); ?></p>
            <div class="hero-ctas">
                <a href="<?php echo esc_url($hero_cta_url ?: get_post_type_archive_link('kurs')); ?>" class="btn-dark">
                    <?php echo esc_html($hero_cta_label ?: 'Unsere Programme'); ?> →
                </a>
            </div>
        </div>
    </div>
</section>

<!-- ══ 2. PROBLEM ════════════════════════════════════════════════════════════ -->
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

<!-- ══ 4. CLIENTS ════════════════════════════════════════════════════════════ -->
<div id="clients" class="fu">
    <div class="clients-inner">
        <p class="clients-headline"><?php echo esc_html($marquee_label); ?></p>
    </div>
    <div class="clients-marquee">
        <div class="clients-track">
            <?php
            $clients_arr   = array_values($clients);
            $clients_reps  = max(2, (int) ceil(16 / max(1, count($clients_arr))) * 2);
            for ($rep = 0; $rep < $clients_reps; $rep++) :
                foreach ($clients_arr as $logo) : ?>
                    <span class="clogo"><?php echo esc_html($logo); ?></span>
                <?php endforeach;
            endfor; ?>
        </div>
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

<!-- ══ SCROLL-FADE JS ════════════════════════════════════════════════════════ -->
<script>
const fuEls = document.querySelectorAll('.fu');
const obs = new IntersectionObserver(entries => {
    entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('vis'); obs.unobserve(e.target); } });
}, { threshold: 0.1 });
fuEls.forEach(el => obs.observe(el));
</script>

<?php get_footer(); ?>
