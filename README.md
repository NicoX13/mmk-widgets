# mmk Widgets

Eigene Elementor-Widgets der Agentur modus. Erscheinen im Elementor-Panel unter der Kategorie **modus Widgets**.

## Widgets

- **mmk Showcase-Slider** — Bildkarten mit Hover-Wachstum und ausblendendem Verlauf. Quellen: manuell, Beiträge nach Kategorie oder handverlesene Beiträge. Wird automatisch zum Scroll-Slider (Punkte, optionale Pfeile, optionales Autoplay), sobald mehr Karten da sind als Plätze.
- **mmk Tab-Slider** — „Unsere Leistungen im Überblick": Panel mit Folien (Headline links, Titel + Beschreibung + optionaler Button rechts), Tab-Leiste unten, Pfeile, optionale Punkte, Autoplay. Hintergrund und Headline pro Folie übersteuerbar, mobil eine Spalte. Shortcode: `[mmk_tab_slider]` mit `[mmk_folie …]`-Einträgen (Details im Kopf von `widgets/tab-slider/shortcode.php`).

## Ohne Elementor (Shortcode)

Die Ausgabe ist von Elementor entkoppelt — auf Seiten ohne Elementor funktioniert derselbe Slider per Shortcode:

```
[mmk_showcase_slider typ="post" kategorien="referenzen" anzahl="6" autoplay="5"]
```

Manuell mit eigenen Karten:

```
[mmk_showcase_slider sichtbar="3" hoehe="480px"]
  [mmk_karte bild="253" titel="Dubbedos" text="Produktdesign, Fotografie" link="https://…"]
  [mmk_karte bild="254" titel="ImmoZirkel" text="Konzept, Website" link="https://…"]
[/mmk_showcase_slider]
```

Alle Attribute stehen im Kopf von `widgets/showcase-slider/shortcode.php`.

## Neues Widget anlegen

1. Neuen Ordner unter `widgets/` anlegen, z. B. `widgets/mein-widget/`
2. Darin eine `widget.php` mit der Klasse `MMK_Widget_Mein_Widget` (Ordnername mit Großbuchstaben und Unterstrichen)
3. Optional `style.scss` und `script.js` dazulegen — beides wird automatisch registriert und nur geladen, wenn das Widget auf der Seite liegt

Mehr ist nicht nötig, das Plugin findet den Ordner von selbst.

## Build (nur für Styles)

```bash
npm install        # einmalig
npm run css        # alle style.scss → style.css
npm run watch      # beim Arbeiten
```

Die kompilierten `style.css` werden mit eingecheckt, damit jeder Repo-Stand direkt lauffähig ist. JavaScript braucht keinen Build.

## Updates über GitHub

Das Plugin prüft das Repo `NicoX13/mmk-widgets` (Branch `main`) auf neue Versionen und bietet sie als normales Plugin-Update in WordPress an. Ablauf für ein Release:

1. Versionsnummer in `mmk-widgets.php` erhöhen (Zeile `Version:` und `MMK_WIDGETS_VERSION`)
2. Committen und pushen

Bei einem **privaten** Repo braucht jede WordPress-Installation einen GitHub-Token in der `wp-config.php`:

```php
define( 'MMK_WIDGETS_GITHUB_TOKEN', 'github_pat_…' );
```

Technik: [plugin-update-checker](https://github.com/YahnisElsts/plugin-update-checker) (liegt unter `includes/lib/`).

## Sicherheit (Grundsätze für alle Widgets)

- **Jede Ausgabe escapen:** Texte `esc_html()`, Links `esc_url()`, Attribute `esc_attr()`. HTML-Tags (Titel-Tag etc.) nur aus fester Whitelist.
- **Abfragen eingrenzen:** Nur veröffentlichte Inhalte öffentlicher Inhaltstypen und Taxonomien, Anzahl gedeckelt. IDs und Zahlen immer `absint()`.
- **Keine Angriffsfläche anbieten:** Keine eigenen Formulare, kein AJAX, keine Datei-Uploads, keine Optionen-Seiten — das Plugin liest nur und gibt aus. Alle PHP-Dateien mit `ABSPATH`-Guard.
- **Keine fremden Quellen:** Kein CDN, keine externen Skripte/Fonts — alles liegt im Plugin.
- **Update-Kanal schützen:** Wer auf das GitHub-Repo schreiben kann, verteilt Code auf alle Sites. Deshalb: GitHub-Konto mit Zwei-Faktor, Token als *fine-grained PAT* nur mit Lese-Recht auf dieses eine Repo, Token NUR in `wp-config.php` (nie committen, nie in die Datenbank).
