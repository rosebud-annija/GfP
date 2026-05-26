# GfP Theme — Project Context

## What This Is

A custom WordPress theme for **Gesellschaft für Personalführung (GfP)**. Built with native PHP (no ACF, no page builders), custom post types, and taxonomy-based filtering. Hosted locally via Local by Flywheel.

**Local path:** `/Users/rosebud/Local Sites/gfp/app/public/wp-content/themes/gfp-theme/`

---

## File Structure

```
gfp-theme/
├── style.css                    ← Main stylesheet + theme header
├── functions.php                ← Theme setup, CPT registration, meta boxes, helpers
├── header.php                   ← Fixed nav (64px height)
├── footer.php                   ← Global footer
├── front-page.php               ← Homepage template
├── page-ueber-uns.php           ← Über-uns page (reference for correct alignment)
├── page-kontakt.php             ← Kontaktseite (Template: Kontaktseite)
├── archive-kurs.php             ← Programmseite /kurse/ — grid + filter
├── single-kurs.php              ← Einzelner Kurs / Workshop
├── inc/                         ← Includes (meta boxes, helpers, etc.)
├── assets/                      ← JS, images
└── template-parts/
    └── kurs-card.php            ← Kurs-Kachel (used in archive-kurs.php)
```

---

## CSS Architecture

### Design Tokens (`:root`)

```css
--max-width:   1280px
--section-px:  clamp(1.5rem, 5vw, 4rem)   /* horizontal page padding */
--font-sans:   …
```

### Fixed Header Clearance

The nav is `position: fixed; height: 64px`. Every page wrapper needs `padding-top: 64px` to avoid content hiding under the nav:

```php
<main class="site-main" style="padding-top: 64px;">
<!-- or -->
<div class="kurs-page" style="padding-top: 64px;">
```

### Two-Level Centering Pattern (IMPORTANT)

The nav uses a two-level structure: padded outer `<header>` + centered inner `.nav-inner`. This means the GfP logo sits at approximately `var(--section-px)` from the left edge of the viewport — **not** at the center margin of a `max-width` container.

To align page content with the logo, content sections must use **one of two patterns**:

**Pattern A — Two-level HTML** (for hero sections with simple block content):
```html
<section class="kurse-hero">           <!-- padding: X var(--section-px) -->
    <div class="kurse-hero__inner">    <!-- max-width: var(--max-width); margin-inline: auto -->
        …content…
    </div>
</section>
```

**Pattern B — calc() trick** (for flex/grid containers where adding a wrapper would break child selectors):
```css
.kurse-grid {
    padding: 0 var(--section-px) 6rem;
    max-width: calc(var(--max-width) + 2 * var(--section-px));
    margin-inline: auto;
}
```

Why `calc(var(--max-width) + 2 * var(--section-px))` works: widening `max-width` by exactly `2 × padding` means the auto-centering offset absorbs the horizontal padding, placing the inner content edge exactly at the logo's left position.

**Never use plain `max-width: var(--max-width)` with `padding-inline: var(--section-px)` at the same level** — this double-indents content relative to the logo.

### Responsive Breakpoints

| Breakpoint | Behavior |
|---|---|
| `max-width: 768px` | Mobile: padding collapses to `24px` hardcoded |

---

## Custom Post Types & Taxonomies

- **`kurs`** — Kurs / Workshop / Programm
  - Taxonomy: **`fachbereich`** (Fachbereich-Filter on archive page)
  - Meta: Dauer, Termine & Ort, price, etc.

---

## Key Helper Functions (functions.php)

| Function | Purpose |
|---|---|
| `gfp_farbe_fuer_name($name)` | Returns color name for a fachbereich term |
| `gfp_farbe_hex($farbe)` | Converts color name to hex value |
| `gfp_kp($key, $default)` | Reads Kontaktseiten-Meta value |

---

## Page-Specific Notes

### archive-kurs.php (`/kurse/`)
- Loads all `fachbereich` terms, sorts them by `$filter_reihenfolge` array
- Renders filter buttons (JS-side, no page reload) + kurs grid
- Filter logic: clicking a `.kurs-filter__btn` toggles visibility of `.kurs-card` elements by matching `data-fachbereich`
- CSS classes: `.kurs-filter`, `.kurse-grid`, `.kurs-card`

### single-kurs.php
- CSS sections: `.kurs-layout` (main info), `.kurs-blocks` (content blocks), `.kurs-back` (back button)
- All use Pattern B (calc trick) for logo alignment

### page-kontakt.php
- Template Name: `Kontaktseite` (set in WordPress page editor)
- Uses `.kontakt-hero` + `.kontakt-hero__inner` (two-level Pattern A)
- Form submits to `admin-post.php` with action `gfp_contact_form`
- `$sent`/`$error` query params drive success/error messages

### page-ueber-uns.php
- **Reference page** for correct alignment — when in doubt, compare to this page
- Uses `.kurs-hero` + `.kurs-hero__inner` two-level pattern

---

## Alignment Change History (May 2026)

Applied to achieve consistent logo-alignment across all pages:

1. **`.kurse-hero`** — padding increased to `7rem var(--section-px) 4rem`; added `.kurse-hero__inner` wrapper with `max-width: var(--max-width); margin-inline: auto`
2. **`.kontakt-hero__inner`** — changed from `max-width: 860px; margin: 0 auto` → `max-width: var(--max-width); margin-inline: auto`
3. **`.kontakt-layout`** — changed from `max-width: 1100px` → `max-width: var(--max-width)`
4. **`.kurs-filter`, `.kurse-grid`, `.kurs-layout`, `.kurs-blocks`, `.kurs-back`** — all changed from `max-width: var(--max-width)` → `max-width: calc(var(--max-width) + 2 * var(--section-px))` (Pattern B)
5. **`archive-kurs.php`** — added `style="padding-top: 64px;"` to `<main>` for fixed-header clearance; wrapped hero content in `.kurse-hero__inner`
