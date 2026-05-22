<?php
defined('ABSPATH') || exit;

// ─── Custom Taxonomy: Fachbereich ─────────────────────────────────────────────

add_action('init', function () {
    register_taxonomy('fachbereich', 'kurs', [
        'labels' => [
            'name'          => __('Fachbereiche', 'gfp'),
            'singular_name' => __('Fachbereich', 'gfp'),
            'all_items'     => __('Alle Fachbereiche', 'gfp'),
            'edit_item'     => __('Fachbereich bearbeiten', 'gfp'),
            'add_new_item'  => __('Neuen Fachbereich anlegen', 'gfp'),
        ],
        'hierarchical'      => true,
        'public'            => true,
        'rewrite'           => ['slug' => 'fachbereich'],
        'show_in_rest'      => true,
        'show_admin_column' => true,
    ]);
});

// ─── Farb-Feld im Bearbeitungsformular ────────────────────────────────────────

add_action('fachbereich_edit_form_fields', function ($term) {
    $farbe = get_term_meta($term->term_id, 'farbe', true) ?: 'blue';
    ?>
    <tr class="form-field">
        <th scope="row"><label for="fachbereich-farbe"><?php _e('Farbe', 'gfp'); ?></label></th>
        <td>
            <select name="fachbereich_farbe" id="fachbereich-farbe">
                <?php
                $farben = [
                    'blue'   => 'Leadership & Führung (#0073BC)',
                    'teal'   => 'Facilitation & Moderation (#23B0A5)',
                    'orange' => 'Teams & Collaboration (#F18712)',
                    'purple' => 'Organisation & Transformation (#9E4493)',
                    'sky'    => 'Personality & Skills (#50C1E0)',
                ];
                foreach ($farben as $wert => $label) {
                    printf(
                        '<option value="%s"%s>%s</option>',
                        esc_attr($wert),
                        selected($farbe, $wert, false),
                        esc_html($label)
                    );
                }
                ?>
            </select>
        </td>
    </tr>
    <?php
});

add_action('fachbereich_add_form_fields', function () {
    ?>
    <div class="form-field">
        <label for="fachbereich-farbe"><?php _e('Farbe', 'gfp'); ?></label>
        <select name="fachbereich_farbe" id="fachbereich-farbe">
            <option value="blue">Leadership & Führung (#0073BC)</option>
            <option value="teal">Facilitation & Moderation (#23B0A5)</option>
            <option value="orange">Teams & Collaboration (#F18712)</option>
            <option value="purple">Organisation & Transformation (#9E4493)</option>
            <option value="sky">Personality & Skills (#50C1E0)</option>
        </select>
    </div>
    <?php
});

add_action('edited_fachbereich', 'gfp_save_fachbereich_meta');
add_action('create_fachbereich', 'gfp_save_fachbereich_meta');

function gfp_save_fachbereich_meta($term_id) {
    if (!isset($_POST['fachbereich_farbe'])) return;
    $erlaubte = ['blue', 'teal', 'orange', 'purple', 'sky'];
    $farbe = sanitize_text_field($_POST['fachbereich_farbe']);
    if (in_array($farbe, $erlaubte, true)) {
        update_term_meta($term_id, 'farbe', $farbe);
    }
}

// ─── Fachbereich-Kategorien beim ersten Adminaufruf anlegen (idempotent) ──────

add_action('admin_init', function (): void {
    $kategorien = [
        ['Leadership & Führung',          'blue'],
        ['Facilitation & Moderation',     'teal'],
        ['Teams & Collaboration',         'orange'],
        ['Organisation & Transformation', 'purple'],
        ['Personality & Skills',          'sky'],
    ];
    foreach ($kategorien as [$name, $farbe]) {
        $term = get_term_by('name', $name, 'fachbereich');
        if (!$term) {
            $result = wp_insert_term($name, 'fachbereich');
            if (!is_wp_error($result)) {
                update_term_meta($result['term_id'], 'farbe', $farbe);
            }
        } else {
            $aktuelle = get_term_meta($term->term_id, 'farbe', true);
            if ($aktuelle !== $farbe) {
                update_term_meta($term->term_id, 'farbe', $farbe);
            }
        }
    }
});
