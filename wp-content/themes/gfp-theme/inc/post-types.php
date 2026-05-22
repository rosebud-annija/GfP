<?php
defined('ABSPATH') || exit;

// ─── Custom Post Type: Kurs ───────────────────────────────────────────────────

add_action('init', function () {
    register_post_type('kurs', [
        'labels' => [
            'name'               => __('Kurse', 'gfp'),
            'singular_name'      => __('Kurs', 'gfp'),
            'add_new'            => __('Neuen Kurs anlegen', 'gfp'),
            'add_new_item'       => __('Neuen Kurs anlegen', 'gfp'),
            'edit_item'          => __('Kurs bearbeiten', 'gfp'),
            'all_items'          => __('Alle Kurse', 'gfp'),
            'search_items'       => __('Kurse durchsuchen', 'gfp'),
            'not_found'          => __('Keine Kurse gefunden', 'gfp'),
        ],
        'public'             => true,
        'has_archive'        => true,
        'rewrite'            => ['slug' => 'kurse'],
        'show_in_rest'       => true,
        'supports'           => ['title', 'editor', 'thumbnail', 'excerpt'],
        'menu_icon'          => 'dashicons-welcome-learn-more',
        'menu_position'      => 5,
    ]);
});

// ─── Custom Post Type: Trainer ────────────────────────────────────────────────

add_action('init', function () {
    register_post_type('trainer', [
        'labels' => [
            'name'          => __('Trainer', 'gfp'),
            'singular_name' => __('Trainer·in', 'gfp'),
            'add_new'       => __('Neue·n Trainer·in anlegen', 'gfp'),
            'add_new_item'  => __('Neue·n Trainer·in anlegen', 'gfp'),
            'edit_item'     => __('Trainer·in bearbeiten', 'gfp'),
            'all_items'     => __('Alle Trainer', 'gfp'),
            'not_found'     => __('Keine Trainer gefunden', 'gfp'),
        ],
        'public'         => false,
        'show_ui'        => true,
        'show_in_menu'   => true,
        'supports'       => ['title', 'thumbnail'],
        'menu_icon'      => 'dashicons-groups',
        'menu_position'  => 6,
    ]);
});

// ─── Block Editor deaktivieren (Theme nutzt Classic Editor + native Meta Boxes) ─

add_filter('use_block_editor_for_post_type', function (bool $enabled, string $type): bool {
    return !in_array($type, ['kurs', 'trainer'], true);
}, 10, 2);
