<?php
/**
 * Template Name: Über uns
 *
 * GfP — Über-uns-Seite
 * WordPress-Seite anlegen, Slug "ueber-uns", Template "Über uns" wählen.
 * Inhalte im Backend unter "Über uns – Seiteninhalte" bearbeiten.
 * Trainer werden automatisch aus dem Trainer-CPT geladen.
 */

defined('ABSPATH') || exit;

$pid = get_queried_object_id();
$uu  = fn(string $key, string $default = ''): string => get_post_meta($pid, '_uu_' . $key, true) ?: $default;

$hero_badge       = $uu('hero_badge',   'GfP');
$hero_title       = $uu('hero_title',   'Über uns');
$intro_text       = $uu('unternehmen_text',
    "Seit die GfP – Gesellschaft für Personalentwicklung – im Jahre 1972 gegründet wurde, hat sich einerseits viel verändert. Und ist auf der anderen Seite viel gleich geblieben. Unverändert ist seit über 45 Jahren unser Pioniergeist, der uns nach wie vor lieber fragen als antworten lässt. Ihm verdanken wir es auch, dass unser Job tagtäglich der spannendste der Welt ist.\n\nVerändert haben sich seit 1972 die Anforderungen, Ziele und Bedürfnisse unserer Kunden, der Wirtschaft generell und der Menschen im Allgemeinen. Veränderungen, die auch uns immer wieder herausfordern.\n\nKurz: wir gehen – engagiert und leidenschaftlich - mit der Zeit. Und obwohl bereits die zweite Generation die Geschicke der GfP lenkt, besinnt sie sich immer wieder ihrer Wurzeln. Denn genau die sind es, die uns zu dem gemacht haben, worauf wir stolz sind: das größte, private Unternehmen in der österreichischen Personal- und Organisationsentwicklungsbranche."
);
$team_heading     = $uu('team_heading',    'Beraterinnen &amp; Trainerinnen');
$team_sub         = $uu('team_sub',        '');
$partner_heading  = $uu('partner_heading', 'Unsere Partner');
$partner_text     = $uu('partner_text',    '');
$partners         = json_decode(get_post_meta($pid, '_uu_partners', true) ?: '[]', true) ?: [];

$trainers = get_posts([
    'post_type'      => 'trainer',
    'posts_per_page' => -1,
    'orderby'        => ['menu_order' => 'ASC', 'title' => 'ASC'],
    'post_status'    => 'publish',
]);

get_header();
?>

<div class="kurs-page" style="padding-top: 64px;">

    <!-- ── Hero (gleiche Struktur wie Kurs-Detailseite) ─────────────────────── -->
    <section class="kurs-hero" style="border-bottom-color: var(--lime);">

        <div class="kurs-hero__inner">

            <!-- Titel -->
            <h1 class="kurs-hero__title"><?php echo esc_html($hero_title); ?></h1>

            <!-- Einleitungstext (Absatz für Absatz) -->
            <?php if ($intro_text) :
                $paragraphs = array_filter(array_map('trim', preg_split('/\n{2,}/', $intro_text)));
                foreach ($paragraphs as $p) : ?>
                    <p class="kurs-hero__sub"><?php echo nl2br(esc_html($p)); ?></p>
                <?php endforeach;
            endif; ?>

        </div>
    </section>

    <!-- ── Beraterinnen & Trainerinnen ──────────────────────────────────────── -->
    <?php if ($trainers) : ?>
    <section class="uu-section uu-team">
        <div class="uu-section__inner">
            <div class="uu-section__head">
                <h2 class="uu-section__title"><?php echo wp_kses_post($team_heading); ?></h2>
                <?php if ($team_sub) : ?>
                    <p class="uu-section__sub"><?php echo esc_html($team_sub); ?></p>
                <?php endif; ?>
            </div>
            <div class="uu-team-grid">
                <?php foreach ($trainers as $trainer) :
                    $role = get_post_meta($trainer->ID, '_trainer_role', true);
                    $bio  = get_post_meta($trainer->ID, '_trainer_bio',  true);
                    $tags = get_post_meta($trainer->ID, '_trainer_tags', true);
                    $foto = get_the_post_thumbnail_url($trainer->ID, [160, 160]);
                ?>
                <div class="uu-trainer-card">
                    <div class="uu-trainer-card__img<?php echo $foto ? '' : ' uu-trainer-card__img--placeholder'; ?>"
                         <?php if ($foto) echo 'style="background-image:url(' . esc_url($foto) . ')"'; ?>
                         aria-hidden="true"></div>
                    <div class="uu-trainer-card__body">
                        <p class="uu-trainer-card__name"><?php echo esc_html($trainer->post_title); ?></p>
                        <?php if ($role) : ?>
                            <p class="uu-trainer-card__role"><?php echo esc_html($role); ?></p>
                        <?php endif; ?>
                        <?php if ($bio) : ?>
                            <p class="uu-trainer-card__bio"><?php echo esc_html($bio); ?></p>
                        <?php endif; ?>
                        <?php if ($tags) : ?>
                            <p class="uu-trainer-card__tags"><?php echo esc_html($tags); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- ── Partner ──────────────────────────────────────────────────────────── -->
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
