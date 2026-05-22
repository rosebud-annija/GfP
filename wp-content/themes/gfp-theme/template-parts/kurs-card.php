<?php
/**
 * template-parts/kurs-card.php
 * GfP — Kurs-Karte
 *
 * Verwendung:
 *   get_template_part('template-parts/kurs-card');
 *
 * Erwartet: den globalen $post (WP Loop oder setup_postdata)
 */

defined('ABSPATH') || exit;

$post_id      = get_the_ID();
$farbe        = gfp_get_kurs_farbe($post_id);
$farbe_hex    = gfp_farbe_hex($farbe);
$fachbereich  = gfp_get_fachbereich($post_id);
$format       = gfp_get_kurs_meta($post_id, 'format')  ?: '';
$trainer      = gfp_get_kurs_meta($post_id, 'trainer') ?: [];
$termine      = gfp_get_kurs_meta($post_id, 'termine') ?: [];
$kommend      = gfp_kommende_termine($termine);
$erster       = !empty($kommend) ? array_values($kommend)[0] : null;

$format_labels = [
    'academy'    => 'Academy',
    'inhouse'    => 'Inhouse',
    'coaching'   => 'Coaching',
    'consulting' => 'Consulting',
];
?>

<a
    href="<?php the_permalink(); ?>"
    class="kurs-card"
    data-fachbereich="<?php echo $fachbereich ? esc_attr($fachbereich->name) : ''; ?>"
    style="border-color: <?php echo esc_attr($farbe_hex); ?>;"
>
    <!-- Farbstreifen oben -->
    <div class="kurs-card__stripe" style="background-color: <?php echo esc_attr($farbe_hex); ?>;"></div>

    <div class="kurs-card__body">

        <!-- Fachbereich + Format -->
        <div class="kurs-card__meta">
            <?php if ($fachbereich) : ?>
                <span class="kurs-card__fachbereich" style="color: <?php echo esc_attr($farbe_hex); ?>;">
                    <?php echo esc_html($fachbereich->name); ?>
                </span>
            <?php endif; ?>
            <?php if ($format) : ?>
                <span class="kurs-card__format">
                    <?php echo esc_html($format_labels[$format] ?? $format); ?>
                </span>
            <?php endif; ?>
        </div>

        <!-- Titel -->
        <h3 class="kurs-card__title"><?php the_title(); ?></h3>

        <!-- Kurzbeschreibung (Excerpt) -->
        <p class="kurs-card__desc"><?php echo esc_html(get_the_excerpt()); ?></p>

        <!-- Trainer-Avatare -->
        <?php if (!empty($trainer)) : ?>
            <div class="kurs-card__trainer">
                <div class="kurs-card__avatars">
                    <?php foreach (array_slice($trainer, 0, 3) as $t) :
                        $foto_url = $t['foto_url'] ?? '';
                        $name     = $t['name']     ?? '';
                    ?>
                        <?php if ($foto_url) : ?>
                            <img
                                src="<?php echo esc_url($foto_url); ?>"
                                alt="<?php echo esc_attr($name); ?>"
                                class="kurs-card__avatar"
                            >
                        <?php else : ?>
                            <div
                                class="kurs-card__avatar--initials"
                                style="background-color: <?php echo esc_attr($farbe_hex); ?>;"
                            >
                                <?php echo esc_html(mb_substr($name, 0, 1)); ?>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
                <span class="kurs-card__trainer-names">
                    <?php echo esc_html(implode(', ', array_column($trainer, 'name'))); ?>
                </span>
            </div>
        <?php endif; ?>

        <!-- Nächster Termin -->
        <?php if ($erster) : ?>
            <div class="kurs-card__termin">
                <span
                    class="kurs-card__termin-dot"
                    style="background-color: <?php echo $erster['ausgebucht'] ? '#555' : esc_attr($farbe_hex); ?>;"
                ></span>
                <span class="kurs-card__termin-text">
                    <?php if ($erster['ausgebucht']) : ?>
                        Ausgebucht
                    <?php else : ?>
                        <?php echo esc_html(gfp_format_datum($erster['datum'], $erster['datum_ende'] ?? '')); ?>
                        <?php if (!empty($erster['ort'])) : ?>
                            &middot; <?php echo esc_html($erster['ort']); ?>
                        <?php endif; ?>
                    <?php endif; ?>
                </span>
            </div>
        <?php endif; ?>

    </div><!-- .kurs-card__body -->
</a>
