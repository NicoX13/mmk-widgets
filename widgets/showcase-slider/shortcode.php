<?php
/**
 * mmk Showcase-Slider als Shortcode — läuft komplett ohne Elementor.
 *
 * Beiträge:  [mmk_showcase_slider typ="post" kategorien="referenzen" anzahl="6" autoplay="5"]
 * Auswahl:   [mmk_showcase_slider auswahl="12,34,56"]
 * Manuell:   [mmk_showcase_slider sichtbar="3"]
 *                [mmk_karte bild="253" titel="Dubbedos" text="Produktdesign, Fotografie" link="https://…"]
 *                [mmk_karte bild="254" titel="ImmoZirkel" text="Konzept, Website" link="https://…"]
 *            [/mmk_showcase_slider]
 *
 * Weitere Attribute: sortierung (datum_neu|datum_alt|titel|menue|zufall),
 * sichtbar, hoehe (z. B. 480px), wachstum (z. B. 1.5), titel_tag, text_tag,
 * punkte (ja/nein), pfeile (ja/nein), verlauf_hover (ja/nein), label.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function mmk_showcase_slider_shortcode_ja( $wert ) {
	return in_array( strtolower( (string) $wert ), [ 'ja', 'yes', 'true', '1' ], true );
}

add_shortcode( 'mmk_karte', function ( $a ) {
	global $mmk_ss_sammler;
	if ( ! is_array( $mmk_ss_sammler ) ) return '';
	$a = shortcode_atts( [ 'bild' => '', 'bild_url' => '', 'titel' => '', 'text' => '', 'link' => '', 'neuer_tab' => 'nein' ], $a );
	$mmk_ss_sammler[] = [
		'bild_id'   => absint( $a['bild'] ),
		'bild_url'  => $a['bild_url'],
		'titel'     => $a['titel'],
		'text'      => $a['text'],
		'url'       => $a['link'],
		'neuer_tab' => mmk_showcase_slider_shortcode_ja( $a['neuer_tab'] ),
		'nofollow'  => false,
	];
	return '';
} );

add_shortcode( 'mmk_showcase_slider', function ( $a, $inhalt = null ) {
	$a = shortcode_atts( [
		'typ'           => 'post',
		'kategorien'    => '',
		'anzahl'        => 6,
		'sortierung'    => 'datum_neu',
		'auswahl'       => '',
		'sichtbar'      => '',
		'hoehe'         => '',
		'wachstum'      => '',
		'autoplay'      => 0,
		'punkte'        => 'ja',
		'pfeile'        => 'nein',
		'verlauf_hover' => 'ja',
		'titel_tag'     => 'h3',
		'text_tag'      => 'p',
		'label'         => 'Referenzen',
	], $a, 'mmk_showcase_slider' );

	// Karten beschaffen: innere [mmk_karte] > auswahl > kategorie
	if ( $inhalt && has_shortcode( $inhalt, 'mmk_karte' ) ) {
		global $mmk_ss_sammler;
		$vorher          = $mmk_ss_sammler;
		$mmk_ss_sammler  = [];
		do_shortcode( $inhalt );
		$karten          = $mmk_ss_sammler;
		$mmk_ss_sammler  = $vorher;
	} elseif ( $a['auswahl'] ) {
		$karten = mmk_showcase_slider_karten_aus_beitraegen( [ 'ids' => explode( ',', $a['auswahl'] ) ] );
	} else {
		// Kategorie-Slugs über alle öffentlichen Taxonomien auflösen
		$terme = [];
		foreach ( array_filter( array_map( 'trim', explode( ',', $a['kategorien'] ) ) ) as $slug ) {
			foreach ( get_taxonomies( [ 'public' => true ], 'names' ) as $tax ) {
				$term = get_term_by( 'slug', $slug, $tax );
				if ( $term ) { $terme[] = $tax . '|' . $term->term_id; break; }
			}
		}
		$karten = mmk_showcase_slider_karten_aus_beitraegen( [
			'post_typ'   => sanitize_key( $a['typ'] ),
			'terme'      => $terme,
			'anzahl'     => $a['anzahl'],
			'sortierung' => $a['sortierung'],
		] );
	}

	// Optik-Attribute als CSS-Variablen direkt am Element
	$style = '';
	if ( $a['sichtbar'] ) $style .= '--ss-sichtbar:' . absint( $a['sichtbar'] ) . ';';
	if ( $a['hoehe'] )    $style .= '--ss-hoehe:' . preg_replace( '/[^0-9a-z.%]/', '', $a['hoehe'] ) . ';';
	if ( $a['wachstum'] ) $style .= '--ss-wachstum:' . floatval( $a['wachstum'] ) . ';';

	wp_enqueue_style( 'mmk-showcase-slider' );
	wp_enqueue_script( 'mmk-showcase-slider' );

	return mmk_showcase_slider_markup( [
		'karten'            => $karten,
		'titel_tag'         => $a['titel_tag'],
		'text_tag'          => $a['text_tag'],
		'bereich_label'     => $a['label'],
		'autoplay_sekunden' => max( 0, floatval( $a['autoplay'] ) ),
		'punkte'            => mmk_showcase_slider_shortcode_ja( $a['punkte'] ),
		'pfeile'            => mmk_showcase_slider_shortcode_ja( $a['pfeile'] ),
		'verlauf_hover_aus' => mmk_showcase_slider_shortcode_ja( $a['verlauf_hover'] ),
		'style'             => $style,
	] );
} );
