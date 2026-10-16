# mmk Widgets

![Version](https://img.shields.io/github/v/tag/NicoX13/mmk-widgets?label=Version&color=8d7d74)
![WordPress](https://img.shields.io/badge/WordPress-6.0%2B-21759b)
![PHP](https://img.shields.io/badge/PHP-7.4%2B-777bb4)
![Lizenz](https://img.shields.io/badge/Lizenz-GPL--2.0--or--later-green)

Eigene Elementor-Widgets der Agentur [modus: medien + kommunikation](https://www.modus-media.de) — gebaut für den eigenen Web-Relaunch, nutzbar auf jeder WordPress-Seite. Alle Widgets erscheinen im Elementor-Panel unter der Kategorie **modus Widgets** und funktionieren **auch komplett ohne Elementor** per Shortcode.

## Inhalt

- [Widgets](#widgets)
- [Voraussetzungen](#voraussetzungen)
- [Installation](#installation)
- [Nutzung in Elementor](#nutzung-in-elementor)
- [Shortcode-Referenz](#shortcode-referenz)
- [Updates über GitHub](#updates-über-github)
- [Eigenes Widget anlegen](#eigenes-widget-anlegen)
- [Entwicklung & Build](#entwicklung--build)
- [Sicherheit](#sicherheit)
- [Lizenz](#lizenz)

## Widgets

### mmk Showcase-Slider

Bildkarten mit Hover-Wachstum und ausblendendem Verlauf — gedacht für Referenzen und Projekt-Teaser.

- Drei Quellen: manuell gepflegte Karten, Beiträge nach Kategorie oder handverlesene Beiträge (IDs)
- Wird automatisch zum Scroll-Slider (Punkte, optionale Pfeile, optionales Autoplay), sobald mehr Karten da sind als sichtbare Plätze
- Höhe, sichtbare Kartenzahl und Hover-Wachstumsfaktor einstellbar

### mmk Tab-Slider

„Unsere Leistungen im Überblick": Panel mit Folien (Headline links, Titel + Beschreibung + optionaler Button rechts) und Tab-Leiste unten.

- Pfeile, optionale Punkte, Autoplay mit Hover-Pause
- Hintergrundfarbe und linke Headline pro Folie übersteuerbar
- Mindesthöhe der Bühne einstellbar (responsive, px/rem/vh oder freie Eingabe)
- Mobil einspaltig

## Voraussetzungen

| | Mindestens |
|---|---|
| WordPress | 6.0 |
| PHP | 7.4 |
| Elementor | optional — nur für die Widget-Bedienung im Builder, Shortcodes laufen ohne |

## Installation

**Als ZIP:** Auf GitHub über *Code → Download ZIP* herunterladen, in WordPress unter *Plugins → Installieren → Plugin hochladen* einspielen, aktivieren.

**Per Git:**

```bash
cd wp-content/plugins
git clone https://github.com/NicoX13/mmk-widgets.git
```

Danach das Plugin im WordPress-Backend aktivieren. Ein Build-Schritt ist nicht nötig — die kompilierten CSS-Dateien liegen im Repo, jeder Stand ist direkt lauffähig.

## Nutzung in Elementor

Beide Widgets liegen im Elementor-Panel unter **modus Widgets** und bringen die üblichen Reiter mit:

- **Inhalt** — Karten bzw. Folien pflegen, Quelle wählen (Showcase), Slider-Verhalten (Autoplay, Pfeile, Punkte)
- **Stil** — Farben, Typografie, Radius; beim Tab-Slider unter *Stil → Folie → Mindesthöhe* die Bühnenhöhe (responsive Regler mit px/rem/vh, über das Bleistift-Symbol auch freie Werte wie `calc(100vh - 10rem)`)

## Shortcode-Referenz

Die Ausgabe ist von Elementor entkoppelt — auf Seiten ohne Elementor funktioniert derselbe Slider per Shortcode.

### `[mmk_showcase_slider]`

Beiträge nach Kategorie:

```
[mmk_showcase_slider typ="post" kategorien="referenzen" anzahl="6" autoplay="5"]
```

Handverlesene Beiträge:

```
[mmk_showcase_slider auswahl="12,34,56"]
```

Manuell mit eigenen Karten:

```
[mmk_showcase_slider sichtbar="3" hoehe="480px"]
  [mmk_karte bild="253" titel="Dubbedos" text="Produktdesign, Fotografie" link="https://…"]
  [mmk_karte bild="254" titel="ImmoZirkel" text="Konzept, Website" link="https://…"]
[/mmk_showcase_slider]
```

| Attribut | Standard | Bedeutung |
|---|---|---|
| `typ` | `post` | Beitragstyp (nur öffentliche Typen) |
| `kategorien` | – | Komma-Liste von Kategorie-Slugs (alle öffentlichen Taxonomien) |
| `anzahl` | `6` | Maximale Beitragszahl |
| `sortierung` | `datum_neu` | `datum_neu`, `datum_alt`, `titel`, `menue`, `zufall` |
| `auswahl` | – | Komma-Liste von Beitrags-IDs (übersteuert `kategorien`) |
| `sichtbar` | automatisch | Gleichzeitig sichtbare Karten |
| `hoehe` | automatisch | Kartenhöhe, z. B. `480px` |
| `wachstum` | – | Hover-Wachstumsfaktor, z. B. `1.5` |
| `autoplay` | `0` | Sekunden pro Karte, `0` = aus |
| `pause` | `ja` | Autoplay pausiert bei Hover |
| `punkte` | `ja` | Punkte-Navigation |
| `pfeile` | `nein` | Pfeil-Navigation |
| `verlauf_hover` | `ja` | Verlauf blendet beim Hover aus |
| `titel_tag` | `h3` | HTML-Tag der Kartentitel |
| `text_tag` | `p` | HTML-Tag der Kartentexte |
| `label` | `Referenzen` | Barrierefreier Name des Sliders |

**`[mmk_karte]`** (innerhalb des Sliders): `bild` (Mediathek-ID), `bild_url` (alternativ direkte URL), `titel`, `text`, `link`, `neuer_tab` (`ja`/`nein`).

### `[mmk_tab_slider]`

```
[mmk_tab_slider headline="Unsere Leistungen im Überblick:" autoplay="7" punkte="ja" mindesthoehe="30rem"]
  [mmk_folie titel="Analyse & Strategie" text="Beschreibung …" button="Mehr erfahren" link="https://…" hintergrund="#deac61"]
  [mmk_folie titel="Design" text="…"]
[/mmk_tab_slider]
```

| Attribut | Standard | Bedeutung |
|---|---|---|
| `headline` | `Unsere Leistungen im Überblick:` | Linke Headline (für alle Folien, pro Folie übersteuerbar) |
| `headline_tag` | `h2` | HTML-Tag der Headline |
| `titel_tag` | `h3` | HTML-Tag der Folientitel |
| `text_tag` | `p` | HTML-Tag der Folientexte |
| `autoplay` | `0` | Sekunden pro Folie, `0` = aus |
| `pause` | `ja` | Autoplay pausiert bei Hover |
| `pfeile` | `ja` | Pfeil-Navigation |
| `punkte` | `nein` | Punkte-Navigation |
| `label` | `Unsere Leistungen` | Barrierefreier Name des Sliders |
| `mindesthoehe` | – | Mindesthöhe der Bühne, z. B. `30rem`, `500px`, `60vh` |

**`[mmk_folie]`** (innerhalb des Sliders): `titel`, `text`, `headline` (abweichende linke Headline), `button` (Button-Text), `link`, `neuer_tab` (`ja`/`nein`), `hintergrund` (Farbe, z. B. `#deac61`).

## Updates über GitHub

Das Plugin prüft dieses Repo auf neue Versionen und bietet sie als normales Plugin-Update in WordPress an — auf jeder Installation, ohne weitere Konfiguration. Technik: [plugin-update-checker](https://github.com/YahnisElsts/plugin-update-checker) (liegt unter `includes/lib/`).

## Eigenes Widget anlegen

1. Neuen Ordner unter `widgets/` anlegen, z. B. `widgets/mein-widget/`
2. Darin eine `widget.php` mit der Klasse `MMK_Widget_Mein_Widget` (Ordnername mit Großbuchstaben und Unterstrichen)
3. Optional `style.scss` und `script.js` dazulegen — beides wird automatisch registriert und nur geladen, wenn das Widget auf der Seite liegt

Mehr ist nicht nötig, das Plugin findet den Ordner von selbst. Empfohlener Aufbau pro Widget: `widget.php` (Elementor-Controls), `render.php` (Markup, von Widget und Shortcode gemeinsam genutzt), `shortcode.php`, `style.scss`, `script.js`.

## Entwicklung & Build

Nur die Styles brauchen einen Build (Sass), JavaScript läuft ohne:

```bash
npm install        # einmalig
npm run css        # alle style.scss → style.css
npm run watch      # beim Arbeiten
```

Die kompilierten `style.css` werden mit eingecheckt, damit jeder Repo-Stand direkt lauffähig ist.

## Sicherheit

Grundsätze für alle Widgets in diesem Plugin:

- **Jede Ausgabe escapen:** Texte `esc_html()`, Links `esc_url()`, Attribute `esc_attr()`. HTML-Tags (Titel-Tag etc.) nur aus fester Whitelist.
- **Abfragen eingrenzen:** Nur veröffentlichte Inhalte öffentlicher Inhaltstypen und Taxonomien, Anzahl gedeckelt. IDs und Zahlen immer `absint()`.
- **Keine Angriffsfläche anbieten:** Keine eigenen Formulare, kein AJAX, keine Datei-Uploads, keine Optionen-Seiten — das Plugin liest nur und gibt aus. Alle PHP-Dateien mit `ABSPATH`-Guard.
- **Keine fremden Quellen:** Kein CDN, keine externen Skripte/Fonts — alles liegt im Plugin.
- **Geschützter Update-Kanal:** Updates kommen ausschließlich aus diesem Repo; der Schreibzugriff darauf ist eng gehalten und abgesichert.

## Lizenz

[GPL-2.0-or-later](LICENSE) — wie WordPress selbst. Frei nutzbar, auch kommerziell; Änderungen und Weitergaben müssen unter derselben Lizenz stehen.

---

Ein Plugin von [modus: medien + kommunikation gmbh](https://www.modus-media.de), Landau in der Pfalz.
