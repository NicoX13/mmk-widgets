<?php
/**
 * mmk Tab-Slider als Shortcode — läuft komplett ohne Elementor.
 *
 * [mmk_tab_slider headline="Unsere Leistungen im Überblick:" autoplay="7" punkte="ja"]
 *     [mmk_folie titel="Analyse & Strategie" text="Beschreibung …" button="Mehr erfahren" link="https://…" hintergrund="#deac61"]
 *     [mmk_folie titel="Design" text="…"]
 * [/mmk_tab_slider]
 *
 * Weitere Attribute: headline_tag, titel_tag, text_tag, pfeile (ja/nein),
 * punkte (ja/nein), pause (ja/nein), label. Pro Folie zusätzlich:
 * headline (abweichende linke Headline), neuer_tab (ja/nein).
 */

if ( ! defined( 'ABSPATH' ) ) exit;

add_shortcode( 'mmk_folie', function ( $a ) {
	global $mmk_ts_sammler;
	if ( ! is_array( $mmk_ts_sammler ) ) return '';
	$a = shortcode_atts( [
		'titel'      => '',
		'text'       => '',
		'headline'   => '',
		'button'     => '',
		'link'       => '',
		'neuer_tab'  => 'nein',
		'hintergrund' => '',
	], $a );
	$mmk_ts_sammler[] = [
		'titel'       => $a['titel'],
		'text'        => $a['text'],
		'headline'    => $a['headline'],
		'button_text' => $a['button'],
		'button_url'  => $a['link'],
		'neuer_tab'   => mmk_widgets_ja( $a['neuer_tab'] ),
		'nofollow'    => false,
		'bg'          => $a['hintergrund'],
	];
	return '';
} );

add_shortcode( 'mmk_tab_slider', function ( $a, $inhalt = null ) {
	$a = shortcode_atts( [
		'headline'     => 'Unsere Leistungen im Überblick:',
		'headline_tag' => 'h2',
		'titel_tag'    => 'h3',
		'text_tag'     => 'p',
		'autoplay'     => 0,
		'pause'        => 'ja',
		'pfeile'       => 'ja',
		'punkte'       => 'nein',
		'label'        => 'Unsere Leistungen',
	], $a, 'mmk_tab_slider' );

	$folien = [];
	if ( $inhalt && has_shortcode( $inhalt, 'mmk_folie' ) ) {
		global $mmk_ts_sammler;
		$vorher         = $mmk_ts_sammler;
		$mmk_ts_sammler = [];
		do_shortcode( $inhalt );
		$folien         = $mmk_ts_sammler;
		$mmk_ts_sammler = $vorher;
	}

	wp_enqueue_style( 'mmk-tab-slider' );
	wp_enqueue_script( 'mmk-tab-slider' );

	return mmk_tab_slider_markup( [
		'headline'          => $a['headline'],
		'headline_tag'      => $a['headline_tag'],
		'titel_tag'         => $a['titel_tag'],
		'text_tag'          => $a['text_tag'],
		'folien'            => $folien,
		'bereich_label'     => $a['label'],
		'autoplay_sekunden' => max( 0, floatval( $a['autoplay'] ) ),
		'pause_bei_hover'   => mmk_widgets_ja( $a['pause'] ),
		'pfeile'            => mmk_widgets_ja( $a['pfeile'] ),
		'punkte'            => mmk_widgets_ja( $a['punkte'] ),
	] );
} );
