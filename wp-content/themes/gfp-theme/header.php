<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<!-- Skip-to-Content für Tastatur-Nutzer (WCAG 2.4.1) -->
<a class="skip-link" href="#main-content">Zum Inhalt springen</a>

<header class="site-header" role="banner">
    <nav class="site-nav" aria-label="Hauptnavigation">

        <!-- Logo -->
        <a href="<?php echo esc_url(home_url('/')); ?>" class="nav-logo" aria-label="GfP — zur Startseite">
            GfP
            <span class="claim" aria-hidden="true">Superheroes,<br>wie wir.</span>
        </a>

        <!-- Desktop-Navigation -->
        <ul class="nav-links" role="list" style="margin-left: auto;">
            <li>
                <?php
                // Kontakt-Seite suchen oder Fallback auf #
                $kontakt = get_page_by_path('kontakt');
                $kontakt_url = $kontakt ? get_permalink($kontakt) : home_url('/#kontakt');
                ?>
                <a href="<?php echo esc_url($kontakt_url); ?>"
                   class="nav-link"
                   <?php echo is_page('kontakt') ? 'aria-current="page"' : ''; ?>>
                    Kontakt
                </a>
            </li>
        </ul>

        <!-- CTA + Burger -->
        <a href="<?php echo esc_url(get_post_type_archive_link('kurs') ?: home_url('/kurse/')); ?>"
           class="nav-cta"
           aria-label="Zum Kursangebot">
            Programm finden
        </a>

        <button class="nav-burger"
                id="navBurger"
                aria-expanded="false"
                aria-controls="navMobile"
                aria-label="Menü öffnen">
            <span></span>
            <span></span>
            <span></span>
        </button>

    </nav>
</header>

<!-- Mobile-Overlay -->
<nav class="nav-mobile" id="navMobile" aria-label="Mobiles Menü" aria-hidden="true">
    <a href="<?php echo esc_url($kontakt_url ?? home_url('/#kontakt')); ?>"
       class="nav-link">
        Kontakt
    </a>
    <a href="<?php echo esc_url(get_post_type_archive_link('kurs') ?: home_url('/kurse/')); ?>"
       class="nav-cta">
        Programm finden
    </a>
</nav>

<script>
// Burger-Menü Toggle
(function () {
    var burger = document.getElementById('navBurger');
    var mobile = document.getElementById('navMobile');
    if (!burger || !mobile) return;

    burger.addEventListener('click', function () {
        var expanded = burger.getAttribute('aria-expanded') === 'true';
        burger.setAttribute('aria-expanded', String(!expanded));
        mobile.setAttribute('aria-hidden', String(expanded));
        mobile.classList.toggle('is-open', !expanded);
        document.body.style.overflow = expanded ? '' : 'hidden';
    });

    // Schließen bei ESC
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && mobile.classList.contains('is-open')) {
            burger.setAttribute('aria-expanded', 'false');
            mobile.setAttribute('aria-hidden', 'true');
            mobile.classList.remove('is-open');
            document.body.style.overflow = '';
            burger.focus();
        }
    });
})();
</script>

<main id="main-content" role="main">
