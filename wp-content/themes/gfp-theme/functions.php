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

add_filter('wp_resource_hints', function ($urls, $relation_type) {
    if ($relation_type === 'preconnect') {
        $urls[] = ['href' => 'https://fonts.googleapis.com', 'crossorigin' => false];
        $urls[] = ['href' => 'https://fonts.gstatic.com', 'crossorigin' => true];
    }
    return $urls;
}, 10, 2);

add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style(
        'gfp-fonts',
        'https://fonts.googleapis.com/css2?family=League+Gothic&family=Host+Grotesk:wght@400;500;600&display=swap',
        [],
        null
    );
    wp_enqueue_style(
        'gfp-style',
        get_stylesheet_uri(),
        ['gfp-fonts'],
        filemtime(get_stylesheet_directory() . '/style.css')
    );

    if (is_post_type_archive('kurs') || is_tax('fachbereich')) {
        wp_enqueue_script(
            'gfp-kurs-filter',
            get_template_directory_uri() . '/assets/js/kurs-filter.js',
            [],
            filemtime(get_template_directory() . '/assets/js/kurs-filter.js'),
            true
        );
    }
});

// ─── WP-Standard-Bloat entfernen (ungenutzte Head-Requests) ──────────────────

add_action('init', function () {
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('admin_print_scripts', 'print_emoji_detection_script');
    remove_action('wp_print_styles', 'print_emoji_styles');
    remove_action('admin_print_styles', 'print_emoji_styles');
    remove_action('wp_head', 'rsd_link');
    remove_action('wp_head', 'wlwmanifest_link');
    remove_action('wp_head', 'wp_shortlink_wp_head');
    remove_action('wp_head', 'wp_generator');
    remove_action('wp_head', 'wp_oembed_add_discovery_links');
    remove_action('wp_head', 'wp_oembed_add_host_js');
});
add_filter('emoji_svg_url', '__return_false');

// ─── Module laden ─────────────────────────────────────────────────────────────

require_once __DIR__ . '/inc/helpers.php';
require_once __DIR__ . '/inc/post-types.php';
require_once __DIR__ . '/inc/taxonomies.php';
require_once __DIR__ . '/inc/meta-trainer.php';
require_once __DIR__ . '/inc/meta-kurs.php';
require_once __DIR__ . '/inc/admin-startseite.php';
require_once __DIR__ . '/inc/kontakt.php';
require_once __DIR__ . '/inc/meta-ueber-uns.php';
