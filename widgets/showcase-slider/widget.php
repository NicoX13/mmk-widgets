<?php
/**
 * mmk Showcase-Slider — Bildkarten mit Breiten-Wachstum beim Hover,
 * Verlauf blendet aus. Bei mehr Einträgen als Plätzen wird gescrollt
 * (nativer Scroll-Snap-Slider, optional Autoplay).
 * Quellen: manuell, Beiträge nach Kategorie oder handverlesene Beiträge.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

require_once __DIR__ . '/render.php';

class MMK_Widget_Showcase_Slider extends \Elementor\Widget_Base {

	public function get_name() {
		return 'mmk-showcase-slider';
	}

	public function get_title() {
		return 'mmk Showcase-Slider';
	}

	public function get_icon() {
		return 'eicon-slider-push';
	}

	public function get_categories() {
		return [ 'mmk' ];
	}

	public function get_keywords() {
		return [ 'mmk', 'modus', 'showcase', 'slider', 'referenzen', 'karten', 'cards' ];
	}

	public function get_style_depends() {
		return [ 'mmk-showcase-slider' ];
	}

	public function get_script_depends() {
		return [ 'mmk-showcase-slider' ];
	}

	// ── Auswahl-Listen für die Regler ──────────────────────────────────

	/** Öffentliche Inhaltstypen (Beiträge, Seiten, eigene Typen). */
	private function post_typ_optionen() {
		$optionen = [];
		foreach ( get_post_types( [ 'public' => true ], 'objects' ) as $typ ) {
			if ( in_array( $typ->name, [ 'attachment', 'elementor_library', 'e-landing-page', 'e-floating-buttons' ], true ) ) continue;
			$optionen[ $typ->name ] = $typ->labels->name;
		}
		return $optionen;
	}

	/** Alle Begriffe öffentlicher Taxonomien, Wert: "taxonomie|term_id". */
	private function term_optionen() {
		$optionen = [];
		foreach ( get_taxonomies( [ 'public' => true ], 'objects' ) as $tax ) {
			if ( 'post_format' === $tax->name ) continue;
			$terme = get_terms( [ 'taxonomy' => $tax->name, 'hide_empty' => false, 'number' => 200 ] );
			if ( is_wp_error( $terme ) ) continue;
			foreach ( $terme as $term ) {
				$optionen[ $tax->name . '|' . $term->term_id ] = $tax->labels->singular_name . ': ' . $term->name;
			}
		}
		return $optionen;
	}

	/** Die letzten 300 Einträge aller öffentlichen Typen zur Handauswahl. */
	private function beitrags_optionen() {
		$optionen = [];
		$beitraege = get_posts( [
			'post_type'      => array_keys( $this->post_typ_optionen() ),
			'posts_per_page' => 300,
			'post_status'    => 'publish',
			'orderby'        => 'date',
			'order'          => 'DESC',
		] );
		foreach ( $beitraege as $beitrag ) {
			$optionen[ $beitrag->ID ] = $beitrag->post_title . ' (' . get_post_type_object( $beitrag->post_type )->labels->singular_name . ')';
		}
		return $optionen;
	}

	// ── Regler ─────────────────────────────────────────────────────────

	protected function register_controls() {

		// Inhalt: Quelle
		$this->start_controls_section( 'bereich_inhalt', [
			'label' => 'Inhalt',
			'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
		] );

		$this->add_control( 'quelle', [
			'label'   => 'Quelle',
			'type'    => \Elementor\Controls_Manager::SELECT,
			'default' => 'manuell',
			'options' => [
				'manuell'   => 'Manuell',
				'kategorie' => 'Beiträge nach Kategorie',
				'auswahl'   => 'Bestimmte Beiträge',
			],
		] );

		$wiederholer = new \Elementor\Repeater();
		$wiederholer->add_control( 'bild', [
			'label'   => 'Bild',
			'type'    => \Elementor\Controls_Manager::MEDIA,
			'default' => [ 'url' => \Elementor\Utils::get_placeholder_image_src() ],
		] );
		$wiederholer->add_control( 'titel', [
			'label'   => 'Titel',
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'Projektname',
		] );
		$wiederholer->add_control( 'text', [
			'label'   => 'Beschreibung',
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'Leistung, Leistung, Leistung',
		] );
		$wiederholer->add_control( 'link', [
			'label' => 'Link',
			'type'  => \Elementor\Controls_Manager::URL,
		] );

		$this->add_control( 'karten', [
			'label'       => 'Karten',
			'type'        => \Elementor\Controls_Manager::REPEATER,
			'fields'      => $wiederholer->get_controls(),
			'title_field' => '{{{ titel }}}',
			'condition'   => [ 'quelle' => 'manuell' ],
			'default'     => [
				[ 'titel' => 'Projekt Eins', 'text' => 'Konzept, Website, Fotografie' ],
				[ 'titel' => 'Projekt Zwei', 'text' => 'Logo, Print, Animation' ],
				[ 'titel' => 'Projekt Drei', 'text' => 'Produktdesign, Webdesign' ],
			],
		] );

		$this->add_control( 'post_typ', [
			'label'     => 'Inhaltstyp',
			'type'      => \Elementor\Controls_Manager::SELECT,
			'default'   => 'post',
			'options'   => $this->post_typ_optionen(),
			'condition' => [ 'quelle' => 'kategorie' ],
		] );

		$this->add_control( 'terme', [
			'label'       => 'Kategorien / Begriffe',
			'description' => 'Leer = alle Einträge des Inhaltstyps.',
			'type'        => \Elementor\Controls_Manager::SELECT2,
			'multiple'    => true,
			'options'     => $this->term_optionen(),
			'condition'   => [ 'quelle' => 'kategorie' ],
		] );

		$this->add_control( 'anzahl', [
			'label'     => 'Maximale Anzahl',
			'type'      => \Elementor\Controls_Manager::NUMBER,
			'default'   => 6,
			'min'       => 1,
			'max'       => 24,
			'condition' => [ 'quelle' => 'kategorie' ],
		] );

		$this->add_control( 'sortierung', [
			'label'     => 'Sortierung',
			'type'      => \Elementor\Controls_Manager::SELECT,
			'default'   => 'datum_neu',
			'options'   => [
				'datum_neu' => 'Datum (neueste zuerst)',
				'datum_alt' => 'Datum (älteste zuerst)',
				'titel'     => 'Titel (A–Z)',
				'menue'     => 'Seiten-Reihenfolge',
				'zufall'    => 'Zufällig',
			],
			'condition' => [ 'quelle' => 'kategorie' ],
		] );

		$this->add_control( 'beitraege', [
			'label'       => 'Beiträge wählen',
			'description' => 'Die Reihenfolge der Auswahl ist die Reihenfolge im Slider.',
			'type'        => \Elementor\Controls_Manager::SELECT2,
			'multiple'    => true,
			'options'     => $this->beitrags_optionen(),
			'condition'   => [ 'quelle' => 'auswahl' ],
		] );

		$this->add_control( 'bereich_label', [
			'label'       => 'Name für Screenreader',
			'description' => 'Beschreibt den Slider für Hilfstechnologien, z. B. "Referenzen".',
			'type'        => \Elementor\Controls_Manager::TEXT,
			'default'     => 'Referenzen',
			'separator'   => 'before',
		] );

		$this->end_controls_section();

		// Inhalt: Darstellung & Slider
		$this->start_controls_section( 'bereich_darstellung', [
			'label' => 'Darstellung & Slider',
			'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
		] );

		$this->add_control( 'titel_tag', [
			'label'   => 'HTML-Tag Titel',
			'type'    => \Elementor\Controls_Manager::SELECT,
			'default' => 'h3',
			'options' => [ 'h1' => 'h1', 'h2' => 'h2', 'h3' => 'h3', 'h4' => 'h4', 'h5' => 'h5', 'h6' => 'h6', 'p' => 'p', 'div' => 'div' ],
		] );

		$this->add_control( 'text_tag', [
			'label'   => 'HTML-Tag Beschreibung',
			'type'    => \Elementor\Controls_Manager::SELECT,
			'default' => 'p',
			'options' => [ 'p' => 'p', 'div' => 'div', 'span' => 'span' ],
		] );

		$this->add_control( 'pfeil_icon', [
			'label'       => 'Pfeil-Icon',
			'description' => 'Leer = eingebauter Pfeil nach schräg oben.',
			'type'        => \Elementor\Controls_Manager::ICONS,
			'skin'        => 'inline',
			'label_block' => false,
		] );

		$this->add_responsive_control( 'sichtbar', [
			'label'          => 'Sichtbare Karten',
			'type'           => \Elementor\Controls_Manager::NUMBER,
			'default'        => 3,
			'tablet_default' => 2,
			'mobile_default' => 1,
			'min'            => 1,
			'max'            => 6,
			'separator'      => 'before',
			'selectors'      => [ '{{WRAPPER}} .mmk-showcase-slider' => '--ss-sichtbar: {{VALUE}};' ],
		] );

		$this->add_control( 'punkte', [
			'label'   => 'Punkte-Navigation',
			'type'    => \Elementor\Controls_Manager::SWITCHER,
			'default' => 'yes',
		] );

		$this->add_control( 'pfeile', [
			'label'   => 'Pfeil-Buttons',
			'type'    => \Elementor\Controls_Manager::SWITCHER,
			'default' => '',
		] );

		$this->add_control( 'autoplay', [
			'label'       => 'Automatisch durchscrollen',
			'description' => 'Pausiert beim Überfahren und bei reduzierter Bewegung (Systemeinstellung).',
			'type'        => \Elementor\Controls_Manager::SWITCHER,
			'default'     => '',
		] );

		$this->add_control( 'autoplay_sekunden', [
			'label'     => 'Sekunden pro Karte',
			'type'      => \Elementor\Controls_Manager::NUMBER,
			'default'   => 5,
			'min'       => 1,
			'max'       => 60,
			'step'      => 0.5,
			'condition' => [ 'autoplay' => 'yes' ],
		] );

		$this->end_controls_section();

		// Stil: Karten
		$this->start_controls_section( 'stil_karten', [
			'label' => 'Karten',
			'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
		] );

		$this->add_responsive_control( 'hoehe', [
			'label'      => 'Höhe',
			'type'       => \Elementor\Controls_Manager::SLIDER,
			'size_units' => [ 'px', 'vh', 'rem' ],
			'range'      => [ 'px' => [ 'min' => 200, 'max' => 900 ], 'vh' => [ 'min' => 20, 'max' => 100 ] ],
			'default'    => [ 'size' => 480, 'unit' => 'px' ],
			'selectors'  => [ '{{WRAPPER}} .mmk-showcase-slider' => '--ss-hoehe: {{SIZE}}{{UNIT}};' ],
		] );

		$this->add_control( 'luecke', [
			'label'      => 'Abstand zwischen Karten',
			'type'       => \Elementor\Controls_Manager::SLIDER,
			'size_units' => [ 'px' ],
			'range'      => [ 'px' => [ 'min' => 0, 'max' => 60 ] ],
			'default'    => [ 'size' => 4, 'unit' => 'px' ],
			'selectors'  => [ '{{WRAPPER}} .mmk-showcase-slider' => '--ss-luecke: {{SIZE}}{{UNIT}};' ],
		] );

		$this->add_control( 'radius', [
			'label'      => 'Eckenradius außen',
			'type'       => \Elementor\Controls_Manager::SLIDER,
			'size_units' => [ 'px', 'rem' ],
			'range'      => [ 'px' => [ 'min' => 0, 'max' => 80 ] ],
			'default'    => [ 'size' => 24, 'unit' => 'px' ],
			'selectors'  => [ '{{WRAPPER}} .mmk-showcase-slider' => '--ss-radius: {{SIZE}}{{UNIT}};' ],
		] );

		$this->add_group_control( \Elementor\Group_Control_Background::get_type(), [
			'name'     => 'verlauf',
			'label'    => 'Verlauf über dem Bild',
			'types'    => [ 'classic', 'gradient' ],
			'selector' => '{{WRAPPER}} .mmk-showcase-slider__verlauf',
		] );

		$this->end_controls_section();

		// Stil: Hover-Effekt
		$this->start_controls_section( 'stil_hover', [
			'label' => 'Hover-Effekt',
			'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
		] );

		$this->add_control( 'wachstum', [
			'label'       => 'Wachstumsfaktor',
			'description' => '1 = kein Wachstum, 1,5 = anderthalbfache Breite.',
			'type'        => \Elementor\Controls_Manager::NUMBER,
			'default'     => 1.5,
			'min'         => 1,
			'max'         => 3,
			'step'        => 0.05,
			'selectors'   => [ '{{WRAPPER}} .mmk-showcase-slider' => '--ss-wachstum: {{VALUE}};' ],
		] );

		$this->add_control( 'dauer', [
			'label'     => 'Dauer (ms)',
			'type'      => \Elementor\Controls_Manager::NUMBER,
			'default'   => 400,
			'min'       => 0,
			'max'       => 2000,
			'step'      => 50,
			'selectors' => [ '{{WRAPPER}} .mmk-showcase-slider' => '--ss-dauer: {{VALUE}}ms;' ],
		] );

		$this->add_control( 'verlauf_hover_aus', [
			'label'   => 'Verlauf beim Hover ausblenden',
			'type'    => \Elementor\Controls_Manager::SWITCHER,
			'default' => 'yes',
		] );

		$this->end_controls_section();

		// Stil: Texte & Icon
		$this->start_controls_section( 'stil_text', [
			'label' => 'Texte & Icon',
			'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
		] );

		$this->add_group_control( \Elementor\Group_Control_Typography::get_type(), [
			'name'     => 'titel_typo',
			'label'    => 'Typografie Titel',
			'selector' => '{{WRAPPER}} .mmk-showcase-slider__titel',
		] );

		$this->add_control( 'titel_farbe', [
			'label'     => 'Farbe Titel',
			'type'      => \Elementor\Controls_Manager::COLOR,
			'selectors' => [ '{{WRAPPER}} .mmk-showcase-slider__titel' => 'color: {{VALUE}};' ],
		] );

		$this->add_group_control( \Elementor\Group_Control_Typography::get_type(), [
			'name'     => 'text_typo',
			'label'    => 'Typografie Beschreibung',
			'selector' => '{{WRAPPER}} .mmk-showcase-slider__text',
		] );

		$this->add_control( 'text_farbe', [
			'label'     => 'Farbe Beschreibung',
			'type'      => \Elementor\Controls_Manager::COLOR,
			'selectors' => [ '{{WRAPPER}} .mmk-showcase-slider__text' => 'color: {{VALUE}};' ],
		] );

		$this->add_control( 'icon_groesse', [
			'label'      => 'Icon-Größe',
			'type'       => \Elementor\Controls_Manager::SLIDER,
			'size_units' => [ 'px', 'rem' ],
			'range'      => [ 'px' => [ 'min' => 10, 'max' => 64 ] ],
			'separator'  => 'before',
			'selectors'  => [ '{{WRAPPER}} .mmk-showcase-slider' => '--ss-icon: {{SIZE}}{{UNIT}};' ],
		] );

		$this->add_control( 'icon_farbe', [
			'label'     => 'Icon-Farbe',
			'type'      => \Elementor\Controls_Manager::COLOR,
			'selectors' => [ '{{WRAPPER}} .mmk-showcase-slider__pfeil' => 'color: {{VALUE}};' ],
		] );

		$this->end_controls_section();

		// Stil: Navigation
		$this->start_controls_section( 'stil_nav', [
			'label' => 'Navigation',
			'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
		] );

		$this->add_control( 'punkt_aktiv', [
			'label'     => 'Punkt aktiv',
			'type'      => \Elementor\Controls_Manager::COLOR,
			'selectors' => [ '{{WRAPPER}} .mmk-showcase-slider' => '--ss-punkt-aktiv: {{VALUE}};' ],
		] );

		$this->add_control( 'punkt_inaktiv', [
			'label'     => 'Punkt inaktiv',
			'type'      => \Elementor\Controls_Manager::COLOR,
			'selectors' => [ '{{WRAPPER}} .mmk-showcase-slider' => '--ss-punkt-rand: {{VALUE}};' ],
		] );

		$this->add_control( 'punkt_groesse', [
			'label'      => 'Punkt-Größe',
			'type'       => \Elementor\Controls_Manager::SLIDER,
			'size_units' => [ 'px' ],
			'range'      => [ 'px' => [ 'min' => 4, 'max' => 24 ] ],
			'selectors'  => [ '{{WRAPPER}} .mmk-showcase-slider' => '--ss-punkt: {{SIZE}}{{UNIT}};' ],
		] );

		$this->add_control( 'pfeil_button_farbe', [
			'label'     => 'Pfeil-Buttons Farbe',
			'type'      => \Elementor\Controls_Manager::COLOR,
			'selectors' => [ '{{WRAPPER}} .mmk-showcase-slider__slider-pfeil' => 'color: {{VALUE}};' ],
			'condition' => [ 'pfeile' => 'yes' ],
		] );

		$this->end_controls_section();
	}

	// ── Daten beschaffen ───────────────────────────────────────────────

	/** Liefert die Karten unabhängig von der Quelle als einheitliche Liste. */
	private function hole_karten( $einstellungen ) {
		if ( 'manuell' === $einstellungen['quelle'] ) {
			$karten = [];
			foreach ( (array) $einstellungen['karten'] as $karte ) {
				$karten[] = [
					'bild_id'  => $karte['bild']['id'] ?? 0,
					'bild_url' => $karte['bild']['url'] ?? '',
					'titel'    => $karte['titel'] ?? '',
					'text'     => $karte['text'] ?? '',
					'url'      => $karte['link']['url'] ?? '',
					'neuer_tab' => ! empty( $karte['link']['is_external'] ),
					'nofollow'  => ! empty( $karte['link']['nofollow'] ),
				];
			}
			return $karten;
		}

		if ( 'auswahl' === $einstellungen['quelle'] ) {
			return mmk_showcase_slider_karten_aus_beitraegen( [ 'ids' => (array) $einstellungen['beitraege'] ] );
		}

		return mmk_showcase_slider_karten_aus_beitraegen( [
			'post_typ'   => $einstellungen['post_typ'],
			'terme'      => (array) $einstellungen['terme'],
			'anzahl'     => $einstellungen['anzahl'],
			'sortierung' => $einstellungen['sortierung'],
		] );
	}

	// ── Ausgabe ────────────────────────────────────────────────────────

	protected function render() {
		$e      = $this->get_settings_for_display();
		$karten = $this->hole_karten( $e );
		if ( ! $karten ) {
			if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
				echo '<p>mmk Showcase-Slider: Die gewählte Quelle liefert keine Einträge.</p>';
			}
			return;
		}

		// Gewähltes Icon als HTML einsammeln, sonst greift der Standard-Pfeil
		$pfeil_html = '';
		if ( ! empty( $e['pfeil_icon']['value'] ) ) {
			ob_start();
			\Elementor\Icons_Manager::render_icon( $e['pfeil_icon'], [ 'aria-hidden' => 'true' ] );
			$pfeil_html = ob_get_clean();
		}

		echo mmk_showcase_slider_markup( [
			'karten'            => $karten,
			'titel_tag'         => \Elementor\Utils::validate_html_tag( $e['titel_tag'] ),
			'text_tag'          => \Elementor\Utils::validate_html_tag( $e['text_tag'] ),
			'bereich_label'     => $e['bereich_label'] ?: 'Referenzen',
			'autoplay_sekunden' => 'yes' === $e['autoplay'] ? max( 1, floatval( $e['autoplay_sekunden'] ?: 5 ) ) : 0,
			'punkte'            => 'yes' === $e['punkte'],
			'pfeile'            => 'yes' === $e['pfeile'],
			'verlauf_hover_aus' => 'yes' === $e['verlauf_hover_aus'],
			'pfeil_html'        => $pfeil_html,
		] );
	}
}
