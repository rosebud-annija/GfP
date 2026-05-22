<?php
/**
 * GfP Theme — functions.php
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

// ─── Fonts & Styles laden ─────────────────────────────────────────────────────

add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style(
        'gfp-fonts',
        'https://fonts.googleapis.com/css2?family=League+Gothic&family=Host+Grotesk:wght@400;500;600&display=swap',
        [],
        null
    );
    wp_enqueue_style('gfp-style', get_stylesheet_uri(), ['gfp-fonts'], '1.0.0');

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

// ─── Module laden ─────────────────────────────────────────────────────────────

require_once __DIR__ . '/inc/helpers.php';
require_once __DIR__ . '/inc/post-types.php';
require_once __DIR__ . '/inc/taxonomies.php';
require_once __DIR__ . '/inc/meta-trainer.php';
require_once __DIR__ . '/inc/meta-kurs.php';
require_once __DIR__ . '/inc/admin-startseite.php';
require_once __DIR__ . '/inc/kontakt.php';
