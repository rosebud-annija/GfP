# GfP WordPress-Projekt — Kontext

## Basics

**GitHub:** https://github.com/rosebud-annija/GfP.git · Branch `main`  
**Lokaler Pfad:** `/Users/rosebud/Local Sites/gfp/app/public/`  
**PHP-Binary:** `/Users/rosebud/Library/Application Support/Local/lightning-services/php-8.2.29+0/bin/darwin-arm64/bin/php` (nicht im System-PATH)  
**MySQL-Socket:** `/Users/rosebud/Library/Application Support/Local/run/jbrjzE07F/mysql/mysqld.sock` · User/PW: `root/root` · DB: `local`

**Git-Workflow:**
```bash
cd "/Users/rosebud/Local Sites/gfp/app/public"
git add wp-content/themes/gfp-theme/
git commit -m "Beschreibung"
git push
```

---

## Dateistruktur

```
wp-content/themes/gfp-theme/
├── style.css                  ← volles Design-System
├── functions.php              ← schlanker Loader
├── front-page.php             ← Startseite
├── archive-kurs.php           ← Kursübersicht mit Filter
├── single-kurs.php            ← Kursdetailseite
├── page-kontakt.php
├── page-ueber-uns.php         ← Template "Über uns" (Referenz für korrektes Alignment)
├── header.php / footer.php
├── template-parts/kurs-card.php
├── assets/js/kurs-filter.js
└── inc/
    ├── helpers.php            ← alle Hilfsfunktionen
    ├── post-types.php         ← CPTs: kurs, trainer
    ├── taxonomies.php         ← Taxonomy: fachbereich
    ├── meta-kurs.php
    ├── meta-trainer.php
    ├── meta-ueber-uns.php
    ├── admin-startseite.php   ← Startseiten-Editor (AJAX, 10 Tabs)
    └── kontakt.php
```

---

## Custom Post Types

### `kurs` — Meta-Felder (`_kurs_*`)
- `_kurs_format` — academy / inhouse / coaching / consulting
- `_kurs_lernziele`, `_kurs_trainer`, `_kurs_termine` — JSON-Arrays
- `_kurs_termine_notiz`, `_kurs_cta_label`, `_kurs_cta_url`
- `_kurs_dauer`, `_kurs_teilnehmer`, `_kurs_investition`, `_kurs_voraussetzungen`, `_kurs_infoabend`
- `_kurs_titel_display` — alternativer Titel mit `|` → `&shy;` (Soft-Hyphen, kein Bindestrich sichtbar, aber Umbruch erlaubt)

### `trainer` — Meta-Felder (`_trainer_*`)
- `_trainer_name_display` — Anzeigename mit `|` → `<wbr>` (Umbrucherlaubnis ohne Bindestrich); leer = Post-Titel
- `_trainer_role`, `_trainer_bio`, `_trainer_tags`
- `_trainer_gruppe` — `team` / `trainerin` / `netzwerk` (steuert Abschnitt auf Über-uns-Seite)
- `_trainer_startseite` — `1` / `0` (erscheint auf Startseite, max. 6)

---

## Taxonomy: `fachbereich`

| Term | Farbe | Hex |
|------|-------|-----|
| Leadership & Führung | blue | #0073BC |
| Facilitation & Moderation | teal | #23B0A5 |
| Teams & Collaboration | orange | #F18712 |
| Organisation & Transformation | purple | #9E4493 |
| Personality & Skills | sky | #50C1E0 |

**⚠️ DB-Problem:** Term-Namen sind in der DB in GROSSBUCHSTABEN + HTML-Entities gespeichert (z. B. `LEADERSHIP &amp; FÜHRUNG`). Nie direkt `$term->name` vergleichen — immer `html_entity_decode` + `mb_strtolower`. Farben immer via `gfp_farbe_fuer_name()`, nie via Term-Meta.

---

## Hilfsfunktionen (`inc/helpers.php`)

```php
gfp_get_kurs_meta(int $post_id, string $field): mixed
gfp_farbe_hex(string $farbe): string               // 'blue' → '#0073BC'
gfp_get_fachbereich(int $post_id): WP_Term|false
gfp_farbe_fuer_name(string $name): string          // normalisiert + Farb-Lookup
gfp_get_kurs_farbe(int $post_id): string
gfp_format_datum(string $iso, string $iso_bis): string
gfp_kommende_termine(array $termine): array
gfp_format_label(string $format): string
gfp_kurs_titel_display(int $post_id): string       // | → &shy;
gfp_trainer_name_display(int $post_id): string     // | → <wbr>
gfp_hp(string $field, string $fallback): string    // liest gfp_hp_* aus wp_options
gfp_kp(string $field, string $fallback): string    // liest _kp_* post meta
```

---

## CSS Design-Tokens (`:root`)

```css
--max-width:   1280px
--section-px:  clamp(1.5rem, 5vw, 4rem)   /* horizontaler Seiten-Innenabstand */
```

> **Achtung Wurzel-Schriftgröße:** `html { font-size: 17px }` (nicht 16px). Dadurch ist der
> Gutter `--section-px` real **25,5px (min) … 68px (max)**, nicht 24–64px. Bei `calc()`-Berechnungen
> (Pattern B) immer daran denken: `--max-width + 2 × 68px = 1416px` bei breitem Viewport.

### Fixed Header — Clearance

Die Nav ist `position: fixed; height: 64px`. Jeder Seiten-Wrapper braucht `padding-top: 64px`:

```php
<main class="site-main" style="padding-top: 64px;">
<!-- oder -->
<div class="kurs-page" style="padding-top: 64px;">
```

---

## Layout-Alignment-Regel (WICHTIG)

Die Nav nutzt ein **Zwei-Ebenen-Muster**: padded outer `<header>` + zentriertes inner `.nav-inner`. Das GfP-Logo sitzt dadurch bei ca. `var(--section-px)` vom linken Viewport-Rand — **nicht** am Zentrier-Margin eines `max-width`-Containers.

Damit Seiteninhalte bündig mit dem Logo beginnen, gibt es zwei Patterns:

### Pattern A — Zwei-Ebenen-HTML
Für Hero-Sektionen mit einfachem Block-Inhalt:
```html
<section class="kurse-hero">           <!-- padding: X var(--section-px) -->
    <div class="kurse-hero__inner">    <!-- max-width: var(--max-width); margin-inline: auto -->
        …content…
    </div>
</section>
```

### Pattern B — calc()-Trick
Für Flex-/Grid-Container, wo ein zusätzlicher Wrapper Kind-Selektoren brechen würde:
```css
.kurse-grid {
    padding: 0 var(--section-px) 6rem;
    max-width: calc(var(--max-width) + 2 * var(--section-px));
    margin-inline: auto;
}
```

**Warum das funktioniert:** `max-width` wird um genau `2 × padding` vergrößert — die Auto-Zentrierung absorbiert den Padding-Offset und platziert die innere Content-Kante exakt auf Logo-Höhe.

**⚠️ Nie** `max-width: var(--max-width)` und `padding-inline: var(--section-px)` auf derselben Ebene kombinieren — das erzeugt eine Doppel-Einrückung gegenüber dem Logo.

**Referenz-Seite:** `page-ueber-uns.php` — diese Seite hat immer korrektes Alignment; bei Zweifeln damit vergleichen.

### Betroffene CSS-Klassen (Stand Juni 2026)

Seit Commit `73b2659` (Juni 2026) liegen **alle** Schienen auf `--max-width` + `--section-px`.
Verifiziert bei 1600px Viewport: Inhaltskante überall `left=160 / right=1440 / width=1280`.

| Klasse | Pattern | Anmerkung |
|--------|---------|-----------|
| `.site-header` + `.site-nav` | A | Globale Nav (alle Seiten) |
| `.site-footer` + `.ft-inner` | A | Globaler Footer — **war vorher 1200px** |
| `.kurse-hero` + `.kurse-hero__inner` | A | Hero der Programmseite |
| `.kontakt-hero` + `.kontakt-hero__inner` | A | Hero der Kontaktseite |
| `.kontakt-layout` / `.kontakt-map` | direkt | `max-width: var(--max-width)` (`.kontakt-map` war 1100px) |
| `.kurs-filter` | B | Filter-Buttons Programmseite |
| `.kurse-grid` | B | Kurs-Kacheln Programmseite |
| `.kurs-layout` | B | Haupt-Layout Kursdetailseite |
| `.kurs-blocks` | B | Content-Blöcke Kursdetailseite |
| `.kurs-back` | B | Zurück-Button Kursdetailseite |
| **Startseite** (`front-page.php`): `.problem-inner`, `.clients-inner`, `.fb-header`, `.trainers-header`, `.trainer-grid`, `.stats-grid`, `.manifest-inner`, `.news-inner` | A | **waren vorher 1200/1100px mit fixem 48px-Gutter** |

**⚠️ Absichtliche Ausnahmen (NICHT auf die Schiene ziehen):**
- **Voll-randige Grids** der Startseite: das 5-farbige Fachbereiche-Grid (`.fb-grid`) und der
  Alfred-Block (`.alfred-main` / `.alfred-intro`) laufen bewusst edge-to-edge. Nur ihre
  *Überschriften* (`.fb-header`, Alfred-Texte) sitzen auf der 1280er-Kante.
- **Lese-Breiten** (Textspalten, kein Layout-Raster): `p { max-width: 68ch }`,
  `.problem-body-text` (760px), `.testi-inner` (820px), `.hero-content` (840px),
  `.hero-body` (480px), diverse `*-sub`-Intros in `ch`. Diese begrenzen die Zeilenlänge
  zur Lesbarkeit und bleiben absichtlich schmaler als die Schiene.

---

## Über-uns-Seite (`page-ueber-uns.php`)

3 Personengruppen aus dem `trainer`-CPT, gefiltert nach `_trainer_gruppe`. Alle drei nutzen `gfp_trainer_name_display()` für den Namen.

| Gruppe | CSS-Grid | Foto |
|--------|----------|------|
| `team` (Firmenmitglieder) | `repeat(auto-fill, minmax(260px, 1fr))` | 120px |
| `trainerin` (Beraterinnen & Trainerinnen) | `repeat(5, 1fr)` · Breakpoints: ≤1300px→4, ≤1024px→3, ≤768px→2, ≤480px→1 | 80px |
| `netzwerk` (Netzwerkpartnerinnen) | `repeat(auto-fill, minmax(220px, 1fr))` | 100px |

`.uu-trainer-card` hat `min-width: 0; width: 100%; box-sizing: border-box` — verhindert Overflow aus `1fr`-Tracks.

**Meta-Felder** (Präfix `_uu_`): `hero_title`, `unternehmen_text`, `firm_heading/sub`, `trainers_heading/sub`, `network_heading/sub`, `partner_heading`, `partner_text`, `partners` (JSON `[{name, url}]`)

---

## Weitere Seiten

**Startseite** (`front-page.php` + `inc/admin-startseite.php`): Speichern per AJAX — **nicht** über Gutenberg-„Aktualisieren". Trainer-Abschnitt lädt CPTs mit `_trainer_startseite = 1`, max. 6.

**Kursdetailseite** (`single-kurs.php`): Sidebar-Reihenfolge: Termine & Ort → Dauer → Teilnehmende → Investition → Voraussetzungen → Infoabend. CTA-Button Farbe = Fachbereich-Hex, inline gesetzt. `.kurs-infocard__notiz` hat dieselbe Farbe wie `.kurs-infocard__wert` (beide Kontexte: allgemein + `body.single-kurs`).

**Kursübersicht** (`archive-kurs.php`): Filterreihenfolge fest kodiert. `data-filter` = normalisierter Lowercase-Name. Filterlink von Startseite: `rawurlencode(mb_strtolower($titel))` als Hash.

**Kontaktseite**: Meta Box nur bei `page-kontakt.php`. Formular-Handler via `action=gfp_contact_form`.

---

## Wichtige Hinweise

- **Kein ACF** — alle Meta Boxes nativ PHP
- **Block-Editor deaktiviert** für `kurs` und `trainer`
- **Fachbereich-Farben** immer via `gfp_farbe_fuer_name()`, nie Term-Meta
- **Footer global** — `footer.php`, alle Templates rufen `get_footer()` auf; Daten via `gfp_hp()`
- **Startseite AJAX** — Gutenberg-Button speichert Meta Box-Daten **nicht**
