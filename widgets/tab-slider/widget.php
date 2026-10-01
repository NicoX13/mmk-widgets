<?php
/**
 * mmk Tab-Slider — „Unsere Leistungen im Überblick":
 * Panel mit Folien (Headline links, Titel + Text + Button rechts),
 * Tab-Leiste unten, Pfeile, optionale Punkte, Autoplay.
 * Alle Tags, Farben und Schriften einstellbar; Standards = Template.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

require_once __DIR__ . '/render.php';

class MMK_Widget_Tab_Slider extends \Elementor\Widget_Base {

	public function get_name() {
		return 'mmk-tab-slider';
	}

	public function get_title() {
		return 'mmk Tab-Slider';
	}

	public function get_icon() {
		return 'eicon-tabs';
	}

	public function get_categories() {
		return [ 'mmk' ];
	}

	public function get_keywords() {
		return [ 'mmk', 'modus', 'tabs', 'slider', 'leistungen', 'services' ];
	}

	public function get_style_depends() {
		return [ 'mmk-tab-slider' ];
	}

	public function get_script_depends() {
		return [ 'mmk-tab-slider' ];
	}

	protected function register_controls() {

		// Inhalt
		$this->start_controls_section( 'bereich_inhalt', [
			'label' => 'Inhalt',
			'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
		] );

		$this->add_control( 'headline', [
			'label'   => 'Headline (links)',
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'Unsere Leistungen im Überblick:',
		] );

		$wiederholer = new \Elementor\Repeater();
		$wiederholer->add_control( 'titel', [
			'label'   => 'Titel (auch Tab-Beschriftung)',
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'Leistung',
		] );
		$wiederholer->add_control( 'text', [
			'label' => 'Beschreibung',
			'type'  => \Elementor\Controls_Manager::TEXTAREA,
			'rows'  => 6,
		] );
		$wiederholer->add_control( 'button_text', [
			'label' => 'Button-Text (optional)',
			'type'  => \Elementor\Controls_Manager::TEXT,
		] );
		$wiederholer->add_control( 'button_link', [
			'label'     => 'Button-Link',
			'type'      => \Elementor\Controls_Manager::URL,
			'condition' => [ 'button_text!' => '' ],
		] );
		$wiederholer->add_control( 'headline_alternativ', [
			'label'       => 'Headline abweichend (optional)',
			'description' => 'Ersetzt die linke Headline nur auf dieser Folie.',
			'type'        => \Elementor\Controls_Manager::TEXT,
		] );
		$wiederholer->add_control( 'hintergrund', [
			'label'       => 'Hintergrund dieser Folie (optional)',
			'type'        => \Elementor\Controls_Manager::COLOR,
		] );

		$this->add_control( 'folien', [
			'label'       => 'Folien',
			'type'        => \Elementor\Controls_Manager::REPEATER,
			'fields'      => $wiederholer->get_controls(),
			'title_field' => '{{{ titel }}}',
			'default'     => [
				[ 'titel' => 'Analyse & Strategie', 'text' => 'Wir stochern nicht im Nebel, sondern sichten fundiert Dein Wettbewerbsumfeld.' ],
				[ 'titel' => 'Design', 'text' => 'Gestaltung, die Deine Marke unverwechselbar macht.' ],
				[ 'titel' => 'Webdesign & Websites', 'text' => 'Websites, die gefunden werden und verkaufen.' ],
			],
		] );

		$this->add_control( 'bereich_label', [
			'label'       => 'Name für Screenreader',
			'description' => 'Beschreibt die Tab-Leiste für Hilfstechnologien.',
			'type'        => \Elementor\Controls_Manager::TEXT,
			'default'     => 'Unsere Leistungen',
			'separator'   => 'before',
		] );

		$this->end_controls_section();

		// Darstellung & Wechsel
		$this->start_controls_section( 'bereich_darstellung', [
			'label' => 'Darstellung & Wechsel',
			'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
		] );

		$tags = [ 'h1' => 'h1', 'h2' => 'h2', 'h3' => 'h3', 'h4' => 'h4', 'h5' => 'h5', 'h6' => 'h6', 'p' => 'p', 'div' => 'div' ];

		$this->add_control( 'headline_tag', [
			'label'   => 'HTML-Tag Headline',
			'type'    => \Elementor\Controls_Manager::SELECT,
			'default' => 'h2',
			'options' => $tags,
		] );

		$this->add_control( 'titel_tag', [
			'label'   => 'HTML-Tag Titel',
			'type'    => \Elementor\Controls_Manager::SELECT,
			'default' => 'h3',
			'options' => $tags,
		] );

		$this->add_control( 'text_tag', [
			'label'   => 'HTML-Tag Beschreibung',
			'type'    => \Elementor\Controls_Manager::SELECT,
			'default' => 'p',
			'options' => [ 'p' => 'p', 'div' => 'div', 'span' => 'span' ],
		] );

		$this->add_control( 'pfeil_icon', [
			'label'       => 'Pfeil-Icon',
			'description' => 'Leer = eingebaute Pfeile. Das Icon wird links gespiegelt.',
			'type'        => \Elementor\Controls_Manager::ICONS,
			'skin'        => 'inline',
			'label_block' => false,
			'separator'   => 'before',
		] );

		$this->add_control( 'pfeile', [
			'label'   => 'Pfeilnavigation',
			'type'    => \Elementor\Controls_Manager::SWITCHER,
			'default' => 'yes',
		] );

		$this->add_control( 'punkte', [
			'label'   => 'Punkte (unterhalb)',
			'type'    => \Elementor\Controls_Manager::SWITCHER,
			'default' => '',
		] );

		$this->add_control( 'autoplay', [
			'label'   => 'Automatisch wechseln',
			'type'    => \Elementor\Controls_Manager::SWITCHER,
			'default' => 'yes',
		] );

		$this->add_control( 'autoplay_sekunden', [
			'label'     => 'Sekunden pro Folie',
			'type'      => \Elementor\Controls_Manager::NUMBER,
			'default'   => 7,
			'min'       => 1,
			'max'       => 60,
			'step'      => 0.5,
			'condition' => [ 'autoplay' => 'yes' ],
		] );

		$this->add_control( 'autoplay_pause', [
			'label'       => 'Pause bei Hover',
			'description' => 'Bei Tastatur-Fokus pausiert das Autoplay immer (Barrierefreiheit).',
			'type'        => \Elementor\Controls_Manager::SWITCHER,
			'default'     => 'yes',
			'condition'   => [ 'autoplay' => 'yes' ],
		] );

		$this->end_controls_section();

		// Stil: Folie
		$this->start_controls_section( 'stil_folie', [
			'label' => 'Folie',
			'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
		] );

		$this->add_control( 'hintergrund_standard', [
			'label'     => 'Hintergrund (Standard)',
			'type'      => \Elementor\Controls_Manager::COLOR,
			'selectors' => [ '{{WRAPPER}} .mmk-tab-slider' => '--tts-bg: {{VALUE}};' ],
		] );

		$this->add_control( 'radius', [
			'label'      => 'Eckenradius',
			'type'       => \Elementor\Controls_Manager::SLIDER,
			'size_units' => [ 'px', 'rem' ],
			'range'      => [ 'px' => [ 'min' => 0, 'max' => 80 ] ],
			'selectors'  => [ '{{WRAPPER}} .mmk-tab-slider' => '--tts-radius: {{SIZE}}{{UNIT}};' ],
		] );

		$this->add_control( 'linie_farbe', [
			'label'     => 'Trennlinie',
			'type'      => \Elementor\Controls_Manager::COLOR,
			'selectors' => [ '{{WRAPPER}} .mmk-tab-slider' => '--tts-linie: {{VALUE}};' ],
		] );

		$this->add_control( 'dauer', [
			'label'     => 'Wechsel-Dauer (ms)',
			'type'      => \Elementor\Controls_Manager::NUMBER,
			'default'   => 450,
			'min'       => 0,
			'max'       => 2000,
			'step'      => 50,
			'selectors' => [ '{{WRAPPER}} .mmk-tab-slider' => '--tts-dauer: {{VALUE}}ms;' ],
		] );

		$this->end_controls_section();

		// Stil: Texte
		$this->start_controls_section( 'stil_texte', [
			'label' => 'Texte',
			'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
		] );

		$this->add_group_control( \Elementor\Group_Control_Typography::get_type(), [
			'name'     => 'headline_typo',
			'label'    => 'Typografie Headline',
			'selector' => '{{WRAPPER}} .mmk-tab-slider__headline',
		] );

		$this->add_control( 'headline_farbe', [
			'label'     => 'Farbe Headline',
			'type'      => \Elementor\Controls_Manager::COLOR,
			'selectors' => [ '{{WRAPPER}} .mmk-tab-slider' => '--tts-headline: {{VALUE}};' ],
		] );

		$this->add_group_control( \Elementor\Group_Control_Typography::get_type(), [
			'name'     => 'titel_typo',
			'label'    => 'Typografie Titel',
			'selector' => '{{WRAPPER}} .mmk-tab-slider__titel',
		] );

		$this->add_control( 'titel_farbe', [
			'label'     => 'Farbe Titel',
			'type'      => \Elementor\Controls_Manager::COLOR,
			'selectors' => [ '{{WRAPPER}} .mmk-tab-slider' => '--tts-titel: {{VALUE}};' ],
		] );

		$this->add_group_control( \Elementor\Group_Control_Typography::get_type(), [
			'name'     => 'text_typo',
			'label'    => 'Typografie Beschreibung',
			'selector' => '{{WRAPPER}} .mmk-tab-slider__text',
		] );

		$this->add_control( 'text_farbe', [
			'label'     => 'Farbe Beschreibung',
			'type'      => \Elementor\Controls_Manager::COLOR,
			'selectors' => [ '{{WRAPPER}} .mmk-tab-slider' => '--tts-text: {{VALUE}};' ],
		] );

		$this->end_controls_section();

		// Stil: Button
		$this->start_controls_section( 'stil_button', [
			'label' => 'Button',
			'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
		] );

		$this->add_group_control( \Elementor\Group_Control_Typography::get_type(), [
			'name'     => 'button_typo',
			'label'    => 'Typografie',
			'selector' => '{{WRAPPER}} .mmk-tab-slider__button',
		] );

		$this->add_control( 'button_bg', [
			'label'     => 'Hintergrund',
			'type'      => \Elementor\Controls_Manager::COLOR,
			'selectors' => [ '{{WRAPPER}} .mmk-tab-slider' => '--tts-button-bg: {{VALUE}};' ],
		] );

		$this->add_control( 'button_farbe', [
			'label'     => 'Textfarbe',
			'type'      => \Elementor\Controls_Manager::COLOR,
			'selectors' => [ '{{WRAPPER}} .mmk-tab-slider' => '--tts-button-text: {{VALUE}};' ],
		] );

		$this->add_control( 'button_hover_bg', [
			'label'       => 'Hintergrund Hover/Fokus',
			'description' => 'Standard: Grau aus dem Template.',
			'type'        => \Elementor\Controls_Manager::COLOR,
			'selectors'   => [ '{{WRAPPER}} .mmk-tab-slider' => '--tts-button-hover-bg: {{VALUE}};' ],
		] );

		$this->add_control( 'button_radius', [
			'label'      => 'Eckenradius',
			'type'       => \Elementor\Controls_Manager::SLIDER,
			'size_units' => [ 'rem', 'px' ],
			'range'      => [ 'rem' => [ 'min' => 0, 'max' => 3, 'step' => 0.125 ], 'px' => [ 'min' => 0, 'max' => 48 ] ],
			'selectors'  => [ '{{WRAPPER}} .mmk-tab-slider' => '--tts-button-radius: {{SIZE}}{{UNIT}};' ],
		] );

		$this->add_responsive_control( 'button_padding', [
			'label'      => 'Padding',
			'type'       => \Elementor\Controls_Manager::DIMENSIONS,
			'size_units' => [ 'rem', 'px' ],
			'selectors'  => [ '{{WRAPPER}} .mmk-tab-slider' => '--tts-button-padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
		] );

		$this->end_controls_section();

		// Stil: Navigation
		$this->start_controls_section( 'stil_nav', [
			'label' => 'Navigation',
			'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
		] );

		$this->add_group_control( \Elementor\Group_Control_Typography::get_type(), [
			'name'     => 'tab_typo',
			'label'    => 'Typografie Tabs',
			'selector' => '{{WRAPPER}} .mmk-tab-slider__tab',
		] );

		$this->add_control( 'tab_farbe', [
			'label'     => 'Tab inaktiv',
			'type'      => \Elementor\Controls_Manager::COLOR,
			'selectors' => [ '{{WRAPPER}} .mmk-tab-slider' => '--tts-tab: {{VALUE}};' ],
		] );

		$this->add_control( 'tab_aktiv', [
			'label'     => 'Tab aktiv',
			'type'      => \Elementor\Controls_Manager::COLOR,
			'selectors' => [ '{{WRAPPER}} .mmk-tab-slider' => '--tts-tab-aktiv: {{VALUE}};' ],
		] );

		$this->add_control( 'tab_hover', [
			'label'       => 'Tab-Schrift Hover/Fokus',
			'description' => 'Leer = gleiche Farbe wie aktiv (weiß).',
			'type'        => \Elementor\Controls_Manager::COLOR,
			'selectors'   => [ '{{WRAPPER}} .mmk-tab-slider' => '--tts-tab-hover: {{VALUE}};' ],
		] );

		$this->add_control( 'tab_bg', [
			'label'       => 'Tab-Hintergrund',
			'description' => 'Standard: kein Hintergrund.',
			'type'        => \Elementor\Controls_Manager::COLOR,
			'selectors'   => [ '{{WRAPPER}} .mmk-tab-slider' => '--tts-tab-bg: {{VALUE}};' ],
		] );

		$this->add_control( 'tab_radius', [
			'label'      => 'Tab-Eckenradius',
			'type'       => \Elementor\Controls_Manager::SLIDER,
			'size_units' => [ 'rem', 'px' ],
			'range'      => [ 'rem' => [ 'min' => 0, 'max' => 3, 'step' => 0.125 ], 'px' => [ 'min' => 0, 'max' => 48 ] ],
			'selectors'  => [ '{{WRAPPER}} .mmk-tab-slider' => '--tts-tab-radius: {{SIZE}}{{UNIT}};' ],
		] );

		$this->add_responsive_control( 'tab_padding', [
			'label'      => 'Tab-Padding',
			'type'       => \Elementor\Controls_Manager::DIMENSIONS,
			'size_units' => [ 'rem', 'px' ],
			'selectors'  => [ '{{WRAPPER}} .mmk-tab-slider' => '--tts-tab-padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
		] );

		$this->add_control( 'tab_spalten_gap', [
			'label'      => 'Abstand zwischen Tabs',
			'type'       => \Elementor\Controls_Manager::SLIDER,
			'size_units' => [ 'rem', 'px' ],
			'range'      => [ 'rem' => [ 'min' => 0, 'max' => 5, 'step' => 0.125 ], 'px' => [ 'min' => 0, 'max' => 80 ] ],
			'selectors'  => [ '{{WRAPPER}} .mmk-tab-slider' => '--tts-tab-spalten-gap: {{SIZE}}{{UNIT}};' ],
		] );

		$this->add_control( 'tab_zeilen_gap', [
			'label'      => 'Abstand zwischen Tab-Zeilen',
			'type'       => \Elementor\Controls_Manager::SLIDER,
			'size_units' => [ 'rem', 'px' ],
			'range'      => [ 'rem' => [ 'min' => 0, 'max' => 3, 'step' => 0.125 ], 'px' => [ 'min' => 0, 'max' => 48 ] ],
			'selectors'  => [ '{{WRAPPER}} .mmk-tab-slider' => '--tts-tab-zeilen-gap: {{SIZE}}{{UNIT}};' ],
		] );

		$this->add_responsive_control( 'tabs_raum', [
			'label'       => 'Abstand Inhalt → Tab-Leiste',
			'description' => 'Platz unter dem Inhalt, in dem die Tab-Leiste sitzt.',
			'type'        => \Elementor\Controls_Manager::SLIDER,
			'size_units'  => [ 'rem', 'px' ],
			'range'       => [ 'rem' => [ 'min' => 3, 'max' => 14, 'step' => 0.25 ], 'px' => [ 'min' => 48, 'max' => 224 ] ],
			'selectors'   => [ '{{WRAPPER}} .mmk-tab-slider' => '--tts-tabs-raum: {{SIZE}}{{UNIT}};' ],
		] );

		$this->add_control( 'pfeil_farbe', [
			'label'     => 'Pfeile',
			'type'      => \Elementor\Controls_Manager::COLOR,
			'separator' => 'before',
			'selectors' => [ '{{WRAPPER}} .mmk-tab-slider' => '--tts-pfeil: {{VALUE}};' ],
		] );

		$this->add_control( 'pfeil_hover_bg', [
			'label'       => 'Pfeile Hintergrund Hover/Fokus',
			'description' => 'Standard: Rot aus dem Template.',
			'type'        => \Elementor\Controls_Manager::COLOR,
			'selectors'   => [ '{{WRAPPER}} .mmk-tab-slider' => '--tts-pfeil-hover-bg: {{VALUE}};' ],
		] );

		$this->add_control( 'pfeil_padding', [
			'label'      => 'Pfeile Padding',
			'type'       => \Elementor\Controls_Manager::SLIDER,
			'size_units' => [ 'rem', 'px' ],
			'range'      => [ 'rem' => [ 'min' => 0, 'max' => 3, 'step' => 0.125 ], 'px' => [ 'min' => 0, 'max' => 48 ] ],
			'default'    => [ 'size' => 0.25, 'unit' => 'rem' ],
			'selectors'  => [ '{{WRAPPER}} .mmk-tab-slider' => '--tts-pfeil-padding: {{SIZE}}{{UNIT}};' ],
		] );

		$this->add_control( 'punkt_aktiv', [
			'label'     => 'Punkt aktiv',
			'type'      => \Elementor\Controls_Manager::COLOR,
			'separator' => 'before',
			'selectors' => [ '{{WRAPPER}} .mmk-tab-slider' => '--tts-punkt-aktiv: {{VALUE}};' ],
		] );

		$this->add_control( 'punkt_inaktiv', [
			'label'     => 'Punkt inaktiv',
			'type'      => \Elementor\Controls_Manager::COLOR,
			'selectors' => [ '{{WRAPPER}} .mmk-tab-slider' => '--tts-punkt-rand: {{VALUE}};' ],
		] );

		$this->add_control( 'punkt_hover', [
			'label'       => 'Hover Punkte',
			'description' => 'Leer = gleiche Farbe wie der aktive Punkt.',
			'type'        => \Elementor\Controls_Manager::COLOR,
			'selectors'   => [ '{{WRAPPER}} .mmk-tab-slider' => '--tts-punkt-hover: {{VALUE}};' ],
		] );

		$this->end_controls_section();
	}

	protected function render() {
		$e = $this->get_settings_for_display();

		$folien = [];
		foreach ( (array) $e['folien'] as $folie ) {
			$folien[] = [
				'titel'       => $folie['titel'] ?? '',
				'text'        => $folie['text'] ?? '',
				'headline'    => $folie['headline_alternativ'] ?? '',
				'button_text' => $folie['button_text'] ?? '',
				'button_url'  => $folie['button_link']['url'] ?? '',
				'neuer_tab'   => ! empty( $folie['button_link']['is_external'] ),
				'nofollow'    => ! empty( $folie['button_link']['nofollow'] ),
				'bg'          => $folie['hintergrund'] ?? '',
			];
		}

		if ( ! $folien ) {
			if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
				echo '<p>mmk Tab-Slider: Noch keine Folien angelegt.</p>';
			}
			return;
		}

		$pfeil_html = '';
		if ( ! empty( $e['pfeil_icon']['value'] ) ) {
			ob_start();
			\Elementor\Icons_Manager::render_icon( $e['pfeil_icon'], [ 'aria-hidden' => 'true' ] );
			$pfeil_html = ob_get_clean();
		}

		echo mmk_tab_slider_markup( [
			'headline'          => $e['headline'],
			'headline_tag'      => \Elementor\Utils::validate_html_tag( $e['headline_tag'] ),
			'titel_tag'         => \Elementor\Utils::validate_html_tag( $e['titel_tag'] ),
			'text_tag'          => \Elementor\Utils::validate_html_tag( $e['text_tag'] ),
			'folien'            => $folien,
			'bereich_label'     => $e['bereich_label'] ?: 'Unsere Leistungen',
			'autoplay_sekunden' => 'yes' === $e['autoplay'] ? max( 1, floatval( $e['autoplay_sekunden'] ?: 7 ) ) : 0,
			'pause_bei_hover'   => 'yes' === $e['autoplay_pause'],
			'pfeile'            => 'yes' === $e['pfeile'],
			'punkte'            => 'yes' === $e['punkte'],
			'pfeil_html'        => $pfeil_html,
		] );
	}
}
