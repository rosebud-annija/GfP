<?php
defined('ABSPATH') || exit;

// ─── Kurs-Metadaten lesen ─────────────────────────────────────────────────────

function gfp_get_kurs_meta(int $post_id, string $field): mixed {
    return match($field) {
        'format'          => get_post_meta($post_id, '_kurs_format',          true) ?: '',
        'lernziele'       => json_decode(get_post_meta($post_id, '_kurs_lernziele', true) ?: '[]', true) ?: [],
        'trainer'         => json_decode(get_post_meta($post_id, '_kurs_trainer',   true) ?: '[]', true) ?: [],
        'termine'         => json_decode(get_post_meta($post_id, '_kurs_termine',   true) ?: '[]', true) ?: [],
        'cta_label'       => get_post_meta($post_id, '_kurs_cta_label',       true) ?: 'Jetzt anmelden',
        'cta_url'         => get_post_meta($post_id, '_kurs_cta_url',         true) ?: '',
        'teilnehmer'      => get_post_meta($post_id, '_kurs_teilnehmer',      true) ?: '',
        'investition'     => get_post_meta($post_id, '_kurs_investition',     true) ?: '',
        'voraussetzungen' => get_post_meta($post_id, '_kurs_voraussetzungen', true) ?: '',
        'infoabend'       => get_post_meta($post_id, '_kurs_infoabend',       true) ?: '',
        'termine_notiz'   => get_post_meta($post_id, '_kurs_termine_notiz',   true) ?: '',
        default           => '',
    };
}

// ─── Fachbereich-Farben ───────────────────────────────────────────────────────

function gfp_farbe_hex(string $farbe): string {
    return match($farbe) {
        'blue'   => '#0073BC',
        'teal'   => '#23B0A5',
        'orange' => '#F18712',
        'purple' => '#9E4493',
        'sky'    => '#50C1E0',
        default  => '#0073BC',
    };
}

function gfp_get_fachbereich(int $post_id = 0): WP_Term|false {
    $post_id = $post_id ?: get_the_ID();
    $terms   = get_the_terms($post_id, 'fachbereich');
    return ($terms && !is_wp_error($terms)) ? $terms[0] : false;
}

function gfp_farbe_fuer_name(string $name): string {
    static $map = [
        'leadership & führung'          => 'blue',
        'facilitation & moderation'     => 'teal',
        'teams & collaboration'         => 'orange',
        'organisation & transformation' => 'purple',
        'personality & skills'          => 'sky',
    ];
    $key = mb_strtolower(html_entity_decode($name, ENT_QUOTES | ENT_HTML5, 'UTF-8'), 'UTF-8');
    return $map[$key] ?? 'blue';
}

function gfp_get_kurs_farbe(int $post_id = 0): string {
    $fachbereich = gfp_get_fachbereich($post_id);
    if (!$fachbereich) return 'blue';
    return gfp_farbe_fuer_name($fachbereich->name);
}

// ─── Datums- und Termin-Helfer ────────────────────────────────────────────────

function gfp_format_datum(string $iso, string $iso_bis = ''): string {
    if (!$iso) return '';
    $fmt = new IntlDateFormatter('de_DE', IntlDateFormatter::LONG, IntlDateFormatter::NONE);
    $von = $fmt->format(new DateTime($iso));
    if ($iso_bis) {
        return $von . ' – ' . $fmt->format(new DateTime($iso_bis));
    }
    return $von;
}

function gfp_kommende_termine(array $termine): array {
    $heute = date('Y-m-d');
    return array_filter($termine, fn($t) => ($t['datum'] ?? '') >= $heute);
}

function gfp_format_label(string $format): string {
    return match($format) {
        'academy'    => 'Academy — offenes Seminar',
        'inhouse'    => 'Inhouse — firmeninternes Training',
        'coaching'   => 'Coaching',
        'consulting' => 'Consulting',
        default      => $format,
    };
}

// ─── Startseiten-Meta lesen ───────────────────────────────────────────────────

function gfp_hp(string $field, string $fallback = ''): string {
    return get_option('gfp_hp_' . $field, '') ?: $fallback;
}

// ─── Kontaktseiten-Meta lesen ─────────────────────────────────────────────────

function gfp_kp(string $field, string $fallback = ''): string {
    $id = (int) get_queried_object_id();
    return get_post_meta($id, '_kp_' . $field, true) ?: $fallback;
}
