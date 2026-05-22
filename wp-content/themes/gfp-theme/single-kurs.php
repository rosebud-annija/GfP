<?php
/**
 * single-kurs.php
 * GfP — Kurs-Detailseite
 * URL: /kurse/{slug}/
 *
 * EIN Template für alle ~60 Kurse.
 * Die Farbe kommt automatisch aus dem zugeordneten Fachbereich.
 *
 * Layout:
 *   [Hero]
 *   [Inhalt (links) | Sidebar: Termine + Infokästchen (rechts)]
 *   [Lernziele + Trainer — volle Breite, Blöcke nebeneinander]
 *   [Zurück-Link]
 */

defined('ABSPATH') || exit;

// ── Daten laden ───────────────────────────────────────────────────────────────
$post_id     = get_the_ID();
$farbe       = gfp_get_kurs_farbe($post_id);
$farbe_hex   = gfp_farbe_hex($farbe);
$fachbereich = gfp_get_fachbereich($post_id);

$format           = gfp_get_kurs_meta($post_id, 'format')           ?: '';
$lernziele        = gfp_get_kurs_meta($post_id, 'lernziele')        ?: [];
$trainer          = gfp_get_kurs_meta($post_id, 'trainer')          ?: [];
$termine          = gfp_get_kurs_meta($post_id, 'termine')          ?: [];
$cta_label        = gfp_get_kurs_meta($post_id, 'cta_label')        ?: 'Jetzt anmelden';
$cta_url          = gfp_get_kurs_meta($post_id, 'cta_url')          ?: '';
$teilnehmer       = gfp_get_kurs_meta($post_id, 'teilnehmer')       ?: '';
$investition      = gfp_get_kurs_meta($post_id, 'investition')      ?: '';
$voraussetzungen  = gfp_get_kurs_meta($post_id, 'voraussetzungen')  ?: '';
$infoabend        = gfp_get_kurs_meta($post_id, 'infoabend')        ?: '';
$termine_notiz    = gfp_get_kurs_meta($post_id, 'termine_notiz')    ?: '';
$dauer            = gfp_get_kurs_meta($post_id, 'dauer')            ?: '';

$kommend = gfp_kommende_termine($termine);

// Sidebar-Infokästchen (nur befüllte Felder — kein Emoji)
$infoboxen = array_filter([
    $dauer           ? ['label' => 'Dauer',           'wert' => $dauer]           : null,
    $teilnehmer      ? ['label' => 'Teilnehmende',    'wert' => $teilnehmer]      : null,
    $investition     ? ['label' => 'Investition',     'wert' => $investition]     : null,
    $voraussetzungen ? ['label' => 'Voraussetzungen', 'wert' => $voraussetzungen] : null,
    $infoabend       ? ['label' => 'Infoabend',       'wert' => $infoabend]       : null,
]);

get_header();
?>

<div class="kurs-page" style="padding-top: 64px;">

    <!-- ── Hero ──────────────────────────────────────────────────────────── -->
    <section
        class="kurs-hero"
        style="border-bottom-color: <?php echo esc_attr($farbe_hex); ?>;"
    >
        <div
            class="kurs-hero__bg-glow"
            style="background: radial-gradient(ellipse at top left, <?php echo esc_attr($farbe_hex); ?>33 0%, transparent 60%);"
        ></div>

        <div class="kurs-hero__inner">

            <!-- Breadcrumb -->
            <nav class="kurs-hero__breadcrumb" aria-label="Breadcrumb">
                <a href="<?php echo esc_url(get_post_type_archive_link('kurs')); ?>">Kurse</a>
                <span aria-hidden="true"> / </span>
                <?php if ($fachbereich) : ?>
                    <span style="color: <?php echo esc_attr($farbe_hex); ?>;">
                        <?php echo esc_html($fachbereich->name); ?>
                    </span>
                <?php endif; ?>
            </nav>

            <!-- Fachbereich-Badge -->
            <?php if ($fachbereich) : ?>
                <span class="kurs-hero__badge" style="background-color: <?php echo esc_attr($farbe_hex); ?>;">
                    <?php echo esc_html($fachbereich->name); ?>
                </span>
            <?php endif; ?>

            <!-- Titel + Excerpt -->
            <h1 class="kurs-hero__title"><?php echo gfp_kurs_titel_display(get_the_ID()); ?></h1>

            <?php if (has_excerpt()) : ?>
                <p class="kurs-hero__sub"><?php echo esc_html(get_the_excerpt()); ?></p>
            <?php endif; ?>

            <?php if ($format) : ?>
                <span class="kurs-hero__format">
                    <?php echo esc_html(gfp_format_label($format)); ?>
                </span>
            <?php endif; ?>

        </div>
    </section>

    <!-- ── Inhalt + Sidebar ─────────────────────────────────────────────── -->
    <div class="kurs-layout">

        <!-- Linke Spalte: Fließtext aus dem Editor -->
        <div class="kurs-layout__main">
            <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
                <?php if (get_the_content()) : ?>
                    <h2 class="kurs-section__title" style="color: <?php echo esc_attr($farbe_hex); ?>;">
                        Über dieses Programm
                    </h2>
                    <div class="kurs-body">
                        <?php the_content(); ?>
                    </div>
                <?php endif; ?>
            <?php endwhile; endif; ?>
        </div>

        <!-- Rechte Spalte: Termine + Infokästchen -->
        <aside class="kurs-layout__sidebar" aria-label="Kursinfos">

            <!-- Termine & Ort -->
            <div class="kurs-infocard" style="border-top-color: <?php echo esc_attr($farbe_hex); ?>;">
                <p class="kurs-infocard__title" style="color: <?php echo esc_attr($farbe_hex); ?>;">Termine &amp; Ort</p>
                <?php if (empty($kommend)) : ?>
                    <p class="kurs-infocard__empty">
                        Aktuell keine offenen Termine.<br>Inhouse-Buchung jederzeit möglich.
                    </p>
                <?php else : ?>
                    <ul class="termine-list">
                        <?php foreach ($kommend as $t) :
                            $ausgebucht = !empty($t['ausgebucht']);
                        ?>
                            <li class="termin-item">
                                <div>
                                    <p class="termin-item__datum <?php echo $ausgebucht ? 'is-ausgebucht' : ''; ?>">
                                        <?php echo esc_html(gfp_format_datum($t['datum'], $t['datum_ende'] ?? '')); ?>
                                    </p>
                                    <?php if (!empty($t['ort'])) : ?>
                                        <p class="termin-item__ort"><?php echo esc_html($t['ort']); ?></p>
                                    <?php endif; ?>
                                    <?php if ($ausgebucht) : ?>
                                        <span class="termin-item__ausgebucht-label">Ausgebucht</span>
                                    <?php endif; ?>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
                <?php if ($termine_notiz) : ?>
                    <p class="kurs-infocard__notiz"><?php echo nl2br(esc_html($termine_notiz)); ?></p>
                <?php endif; ?>

                <!-- CTA -->
                <?php if ($cta_url) : ?>
                    <a href="<?php echo esc_url($cta_url); ?>"
                       class="kurs-cta-btn"
                       style="background-color: <?php echo esc_attr($farbe_hex); ?>; color: #fff;"
                       target="_blank" rel="noopener noreferrer">
                        <?php echo esc_html($cta_label); ?>
                    </a>
                <?php else : ?>
                    <p class="kurs-infocard__empty" style="margin-top:.75rem;">
                        Für Buchungen: <a href="mailto:info@gfp.de" style="color: <?php echo esc_attr($farbe_hex); ?>;">info@gfp.de</a>
                    </p>
                <?php endif; ?>
            </div>

            <!-- Infokästchen (Teilnehmer, Investition, …) -->
            <?php foreach ($infoboxen as $box) : ?>
                <div class="kurs-infocard" style="border-top-color: <?php echo esc_attr($farbe_hex); ?>;">
                    <p class="kurs-infocard__title" style="color: <?php echo esc_attr($farbe_hex); ?>;"><?php echo esc_html($box['label']); ?></p>
                    <p class="kurs-infocard__wert"><?php echo nl2br(esc_html($box['wert'])); ?></p>
                </div>
            <?php endforeach; ?>

        </aside>

    </div><!-- .kurs-layout -->

    <!-- ── Lernziele + Trainer — volle Breite ────────────────────────────── -->
    <?php if (!empty($lernziele) || !empty($trainer)) : ?>
    <div class="kurs-blocks" style="--farbe: <?php echo esc_attr($farbe_hex); ?>;">

        <!-- Lernziele -->
        <?php if (!empty($lernziele)) : ?>
        <div class="kurs-block">
            <h2 class="kurs-block__title" style="color: <?php echo esc_attr($farbe_hex); ?>;">
                Was du mitnimmst
            </h2>
            <ul class="lernziele">
                <?php foreach ($lernziele as $ziel) : ?>
                    <li class="lernziele__item">
                        <span class="lernziele__dot" style="background-color: <?php echo esc_attr($farbe_hex); ?>;"></span>
                        <span><?php echo esc_html(is_array($ziel) ? ($ziel['text'] ?? '') : $ziel); ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <!-- Trainer -->
        <?php if (!empty($trainer)) : ?>
        <div class="kurs-block">
            <h2 class="kurs-block__title" style="color: <?php echo esc_attr($farbe_hex); ?>;">
                <?php echo count($trainer) === 1 ? 'Dein / Deine Trainer·in' : 'Trainer &amp; Trainerinnen'; ?>
            </h2>
            <div class="trainer-list">
                <?php foreach ($trainer as $t) :
                    $foto_url = $t['foto_url'] ?? '';
                    $name     = $t['name']     ?? '';
                    $rolle    = $t['rolle']    ?? '';
                    $bio      = $t['bio']      ?? '';
                ?>
                    <div class="trainer-item">
                        <?php if ($foto_url) : ?>
                            <img src="<?php echo esc_url($foto_url); ?>"
                                 alt="<?php echo esc_attr($name); ?>"
                                 class="trainer-item__foto">
                        <?php else : ?>
                            <div class="trainer-item__initials"
                                 style="background-color: <?php echo esc_attr($farbe_hex); ?>;">
                                <?php echo esc_html(mb_substr($name, 0, 1)); ?>
                            </div>
                        <?php endif; ?>
                        <div>
                            <p class="trainer-item__name"><?php echo esc_html($name); ?></p>
                            <?php if ($rolle) : ?>
                                <p class="trainer-item__role"><?php echo esc_html($rolle); ?></p>
                            <?php endif; ?>
                            <?php if ($bio) : ?>
                                <p class="trainer-item__bio"><?php echo esc_html($bio); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

    </div><!-- .kurs-blocks -->
    <?php endif; ?>

    <!-- ── Zurück ────────────────────────────────────────────────────────── -->
    <div class="kurs-back">
        <a href="<?php echo esc_url(get_post_type_archive_link('kurs')); ?>" class="kurs-back__link">
            ← Zurück zur Kursübersicht
        </a>
    </div>

</div><!-- .kurs-page -->

<?php get_footer(); ?>
