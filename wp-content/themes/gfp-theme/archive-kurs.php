<?php
/**
 * archive-kurs.php
 * GfP — Kurs-Übersichtsseite
 * URL: /kurse/
 *
 * Zeigt alle Kurse als Grid, filterbar nach Fachbereich (JS-seitig ohne Reload).
 * Fachbereiche werden automatisch aus den vorhandenen Kursen ermittelt.
 */

defined('ABSPATH') || exit;

// Alle Fachbereiche mit mindestens einem Kurs laden
$fachbereiche = get_terms([
    'taxonomy'            => 'fachbereich',
    'hide_empty'          => true,
    'cache_results'       => false,
    'update_term_meta_cache' => false,
]);

// Gewünschte Reihenfolge der Filter-Buttons (lowercase für Vergleich)
$filter_reihenfolge = [
    'leadership & führung',
    'facilitation & moderation',
    'teams & collaboration',
    'organisation & transformation',
    'personality & skills',
];
if (!is_wp_error($fachbereiche)) {
    usort($fachbereiche, function ($a, $b) use ($filter_reihenfolge) {
        $norm_a = mb_strtolower(html_entity_decode($a->name, ENT_QUOTES | ENT_HTML5, 'UTF-8'), 'UTF-8');
        $norm_b = mb_strtolower(html_entity_decode($b->name, ENT_QUOTES | ENT_HTML5, 'UTF-8'), 'UTF-8');
        $pos_a  = array_search($norm_a, $filter_reihenfolge);
        $pos_b  = array_search($norm_b, $filter_reihenfolge);
        $pos_a  = $pos_a === false ? 999 : $pos_a;
        $pos_b  = $pos_b === false ? 999 : $pos_b;
        return $pos_a - $pos_b;
    });
}

// Anzahl aller Kurse
$args = [
    'post_type'      => 'kurs',
    'posts_per_page' => -1,
    'post_status'    => 'publish',
    'orderby'        => ['meta_value_num' => 'DESC', 'title' => 'ASC'],
];
$alle_kurse_query = new WP_Query($args);

get_header();
?>

<main class="site-main">

    <!-- ── Hero ──────────────────────────────────────────────────────────── -->
    <section class="kurse-hero">
        <p class="kurse-hero__label">Alle Programme</p>
        <h1 class="kurse-hero__title">Kurse &amp;<br>Programme</h1>
        <p class="kurse-hero__sub">
            <?php echo $alle_kurse_query->found_posts; ?> Programme in
            <?php echo count($fachbereiche); ?> Fachbereichen —
            von Leadership bis Facilitation, von Einzelcoaching bis Inhouse-Training.
        </p>
    </section>

    <!-- ── Filter-Buttons ────────────────────────────────────────────────── -->
    <?php if (!empty($fachbereiche) && !is_wp_error($fachbereiche)) : ?>
        <div class="kurs-filter" role="group" aria-label="Nach Fachbereich filtern">

            <button
                class="kurs-filter__btn is-active"
                data-filter="all"
                style="background:#fff; color:#111; border-color:#fff;"
            >
                Alle (<?php echo $alle_kurse_query->found_posts; ?>)
            </button>

            <?php foreach ($fachbereiche as $fb) :
                $farbe     = gfp_farbe_fuer_name($fb->name);
                $farbe_hex = gfp_farbe_hex($farbe);
                $anzahl    = $fb->count;
            ?>
                <button
                    class="kurs-filter__btn"
                    data-filter="<?php echo esc_attr($fb->name); ?>"
                    data-farbe-hex="<?php echo esc_attr($farbe_hex); ?>"
                    style="border-color: <?php echo esc_attr($farbe_hex); ?>; color: <?php echo esc_attr($farbe_hex); ?>;"
                >
                    <?php echo esc_html($fb->name); ?> (<?php echo $anzahl; ?>)
                </button>
            <?php endforeach; ?>

        </div>
    <?php endif; ?>

    <!-- ── Kurs-Grid ─────────────────────────────────────────────────────── -->
    <div class="kurse-grid" id="kurse-grid">

        <?php if ($alle_kurse_query->have_posts()) : ?>
            <?php while ($alle_kurse_query->have_posts()) : $alle_kurse_query->the_post(); ?>
                <?php get_template_part('template-parts/kurs-card'); ?>
            <?php endwhile; ?>
            <?php wp_reset_postdata(); ?>
        <?php else : ?>
            <p class="text-muted" style="grid-column: 1/-1; text-align:center; padding: 4rem 0;">
                Noch keine Kurse vorhanden.
            </p>
        <?php endif; ?>

    </div><!-- #kurse-grid -->

</main>

<?php get_footer(); ?>
