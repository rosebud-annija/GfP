<?php
/**
 * Template Name: Über uns
 *
 * GfP — Über-uns-Seite
 * WordPress-Seite anlegen, Slug "ueber-uns", Template "Über uns" wählen.
 * Inhalte im Backend unter "Über uns – Seiteninhalte" bearbeiten.
 * Personen werden aus dem Trainer-CPT geladen, nach Gruppe gefiltert.
 */

defined('ABSPATH') || exit;

$pid = get_queried_object_id();
$uu  = fn(string $key, string $default = ''): string => get_post_meta($pid, '_uu_' . $key, true) ?: $default;

$hero_title      = $uu('hero_title',   'Über uns');
$intro_text      = $uu('unternehmen_text',
    "Seit die GfP – Gesellschaft für Personalentwicklung – im Jahre 1972 gegründet wurde, hat sich einerseits viel verändert. Und ist auf der anderen Seite viel gleich geblieben. Unverändert ist seit über 45 Jahren unser Pioniergeist, der uns nach wie vor lieber fragen als antworten lässt. Ihm verdanken wir es auch, dass unser Job tagtäglich der spannendste der Welt ist.\n\nVerändert haben sich seit 1972 die Anforderungen, Ziele und Bedürfnisse unserer Kunden, der Wirtschaft generell und der Menschen im Allgemeinen. Veränderungen, die auch uns immer wieder herausfordern.\n\nKurz: wir gehen – engagiert und leidenschaftlich - mit der Zeit. Und obwohl bereits die zweite Generation die Geschicke der GfP lenkt, besinnt sie sich immer wieder ihrer Wurzeln. Denn genau die sind es, die uns zu dem gemacht haben, worauf wir stolz sind: das größte, private Unternehmen in der österreichischen Personal- und Organisationsentwicklungsbranche."
);

$firm_heading    = $uu('firm_heading',    'Unser Team');
$firm_sub        = $uu('firm_sub',        '');
$trainers_heading = $uu('trainers_heading', 'Beraterinnen &amp; Trainerinnen');
$trainers_sub    = $uu('trainers_sub',    '');
$network_heading = $uu('network_heading', 'Netzwerkpartnerinnen &amp; -partner');
$network_sub     = $uu('network_sub',     '');

$partner_heading = $uu('partner_heading', 'Unsere Partner');
$partner_text    = $uu('partner_text',    '');
$partners        = json_decode(get_post_meta($pid, '_uu_partners', true) ?: '[]', true) ?: [];

$base_query = [
    'post_type'      => 'trainer',
    'posts_per_page' => -1,
    'orderby'        => ['menu_order' => 'ASC', 'title' => 'ASC'],
    'post_status'    => 'publish',
];

$firmenmitglieder    = get_posts($base_query + ['meta_query' => [['key' => '_trainer_gruppe', 'value' => 'team']]]);
$trainerinnen        = get_posts($base_query + ['meta_query' => [['key' => '_trainer_gruppe', 'value' => 'trainerin']]]);
$netzwerkpartnerinnen = get_posts($base_query + ['meta_query' => [['key' => '_trainer_gruppe', 'value' => 'netzwerk']]]);

get_header();
?>

<div class="kurs-page" style="padding-top: 64px;">

    <!-- ── Hero ────────────────────────────────────────────────────────────────── -->
    <section class="kurs-hero" style="border-bottom-color: var(--lime);">
        <div class="kurs-hero__inner">
            <h1 class="kurs-hero__title"><?php echo esc_html($hero_title); ?></h1>
            <?php if ($intro_text) :
                $paragraphs = array_filter(array_map('trim', preg_split('/\n{2,}/', $intro_text)));
                foreach ($paragraphs as $p) : ?>
                    <p class="kurs-hero__sub"><?php echo nl2br(esc_html($p)); ?></p>
                <?php endforeach;
            endif; ?>
        </div>
    </section>

    <!-- ── Firmenmitglieder ─────────────────────────────────────────────────────── -->
    <?php if ($firmenmitglieder) : ?>
    <section class="uu-section uu-team uu-team--firm">
        <div class="uu-section__inner">
            <div class="uu-section__head">
                <h2 class="uu-section__title"><?php echo wp_kses_post($firm_heading); ?></h2>
                <?php if ($firm_sub) : ?>
                    <p class="uu-section__sub"><?php echo esc_html($firm_sub); ?></p>
                <?php endif; ?>
            </div>
            <div class="uu-team-grid uu-team-grid--firm">
                <?php foreach ($firmenmitglieder as $person) :
                    $role = get_post_meta($person->ID, '_trainer_role', true);
                    $bio  = get_post_meta($person->ID, '_trainer_bio',  true);
                    $foto = get_the_post_thumbnail_url($person->ID, [240, 240]);
                ?>
                <div class="uu-trainer-card uu-trainer-card--lg">
                    <div class="uu-trainer-card__img uu-trainer-card__img--lg<?php echo $foto ? '' : ' uu-trainer-card__img--placeholder'; ?>"
                         <?php if ($foto) echo 'style="background-image:url(' . esc_url($foto) . ')"'; ?>
                         aria-hidden="true"></div>
                    <div class="uu-trainer-card__body">
                        <p class="uu-trainer-card__name"><?php echo esc_html($person->post_title); ?></p>
                        <?php if ($role) : ?>
                            <p class="uu-trainer-card__role"><?php echo esc_html($role); ?></p>
                        <?php endif; ?>
                        <?php if ($bio) : ?>
                            <p class="uu-trainer-card__bio"><?php echo esc_html($bio); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- ── Beraterinnen & Trainerinnen ──────────────────────────────────────────── -->
    <?php if ($trainerinnen) : ?>
    <section class="uu-section uu-team uu-team--trainers">
        <div class="uu-section__inner">
            <div class="uu-section__head">
                <h2 class="uu-section__title"><?php echo wp_kses_post($trainers_heading); ?></h2>
                <?php if ($trainers_sub) : ?>
                    <p class="uu-section__sub"><?php echo esc_html($trainers_sub); ?></p>
                <?php endif; ?>
            </div>
            <div class="uu-team-grid uu-team-grid--trainers">
                <?php foreach ($trainerinnen as $person) :
                    $role = get_post_meta($person->ID, '_trainer_role', true);
                    $bio  = get_post_meta($person->ID, '_trainer_bio',  true);
                    $foto = get_the_post_thumbnail_url($person->ID, [160, 160]);
                ?>
                <div class="uu-trainer-card">
                    <div class="uu-trainer-card__img<?php echo $foto ? '' : ' uu-trainer-card__img--placeholder'; ?>"
                         <?php if ($foto) echo 'style="background-image:url(' . esc_url($foto) . ')"'; ?>
                         aria-hidden="true"></div>
                    <div class="uu-trainer-card__body">
                        <p class="uu-trainer-card__name"><?php echo esc_html($person->post_title); ?></p>
                        <?php if ($role) : ?>
                            <p class="uu-trainer-card__role"><?php echo esc_html($role); ?></p>
                        <?php endif; ?>
                        <?php if ($bio) : ?>
                            <p class="uu-trainer-card__bio"><?php echo esc_html($bio); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- ── Netzwerkpartnerinnen ─────────────────────────────────────────────────── -->
    <?php if ($netzwerkpartnerinnen) : ?>
    <section class="uu-section uu-team uu-team--network">
        <div class="uu-section__inner">
            <div class="uu-section__head">
                <h2 class="uu-section__title"><?php echo wp_kses_post($network_heading); ?></h2>
                <?php if ($network_sub) : ?>
                    <p class="uu-section__sub"><?php echo esc_html($network_sub); ?></p>
                <?php endif; ?>
            </div>
            <div class="uu-team-grid uu-team-grid--network">
                <?php foreach ($netzwerkpartnerinnen as $person) :
                    $role = get_post_meta($person->ID, '_trainer_role', true);
                    $bio  = get_post_meta($person->ID, '_trainer_bio',  true);
                    $foto = get_the_post_thumbnail_url($person->ID, [200, 200]);
                ?>
                <div class="uu-trainer-card uu-trainer-card--md">
                    <div class="uu-trainer-card__img uu-trainer-card__img--md<?php echo $foto ? '' : ' uu-trainer-card__img--placeholder'; ?>"
                         <?php if ($foto) echo 'style="background-image:url(' . esc_url($foto) . ')"'; ?>
                         aria-hidden="true"></div>
                    <div class="uu-trainer-card__body">
                        <p class="uu-trainer-card__name"><?php echo esc_html($person->post_title); ?></p>
                        <?php if ($role) : ?>
                            <p class="uu-trainer-card__role"><?php echo esc_html($role); ?></p>
                        <?php endif; ?>
                        <?php if ($bio) : ?>
                            <p class="uu-trainer-card__bio"><?php echo esc_html($bio); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- ── Partner (Pill-Liste, unverändert) ────────────────────────────────────── -->
    <?php if ($partners) : ?>
    <section class="uu-section uu-partner">
        <div class="uu-section__inner">
            <div class="uu-section__head">
                <h2 class="uu-section__title"><?php echo esc_html($partner_heading); ?></h2>
                <?php if ($partner_text) : ?>
                    <p class="uu-section__sub"><?php echo nl2br(esc_html($partner_text)); ?></p>
                <?php endif; ?>
            </div>
            <ul class="uu-partner-grid">
                <?php foreach ($partners as $p) :
                    if (empty($p['name'])) continue;
                ?>
                <li class="uu-partner-item">
                    <?php if (!empty($p['url'])) : ?>
                        <a href="<?php echo esc_url($p['url']); ?>"
                           target="_blank" rel="noopener noreferrer"
                           class="uu-partner-link">
                            <?php echo esc_html($p['name']); ?>
                        </a>
                    <?php else : ?>
                        <span class="uu-partner-link"><?php echo esc_html($p['name']); ?></span>
                    <?php endif; ?>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </section>
    <?php endif; ?>

</div>

<?php get_footer(); ?>
