<?php
/**
 * mmk Showcase-Slider — gemeinsame Ausgabe.
 * Wird vom Elementor-Widget UND vom Shortcode genutzt, dadurch läuft der
 * Slider auch komplett ohne Elementor. Karten kommen immer als
 * einheitliche Liste: bild_id, bild_url, titel, text, url, neuer_tab, nofollow.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/** Eingebauter Pfeil nach schräg oben (Fallback ohne gewähltes Icon). */
function mmk_showcase_slider_pfeil_standard() {
	return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7 17L17 7M9 7h8v8"/></svg>';
}

/** Beiträge als Karten holen (Quelle "kategorie" oder "auswahl"). */
function mmk_showcase_slider_karten_aus_beitraegen( array $a ) {
	$abfrage = [
		'post_status'         => 'publish',
		'ignore_sticky_posts' => true,
	];

	// Sicherheit: nur öffentliche Inhaltstypen, egal was in den
	// Einstellungen steht — nichts Internes (Blöcke, Entwürfe o. Ä.) leaken.
	$oeffentliche_typen = array_values( get_post_types( [ 'public' => true ] ) );

	if ( ! empty( $a['ids'] ) ) {
		$ids = array_filter( array_map( 'absint', (array) $a['ids'] ) );
		if ( ! $ids ) return [];
		$abfrage['post_type']      = $oeffentliche_typen;
		$abfrage['post__in']       = $ids;
		$abfrage['orderby']        = 'post__in';
		$abfrage['posts_per_page'] = count( $ids );
	} else {
		$typ = sanitize_key( $a['post_typ'] ?? 'post' );
		$abfrage['post_type']      = in_array( $typ, $oeffentliche_typen, true ) ? $typ : 'post';
		$abfrage['posts_per_page'] = min( 50, max( 1, absint( $a['anzahl'] ?? 6 ) ) );

		// Begriffe nach Taxonomie bündeln, Einträge wie "category|12"
		$nach_tax = [];
		foreach ( (array) ( $a['terme'] ?? [] ) as $wert ) {
			if ( strpos( $wert, '|' ) === false ) continue;
			list( $tax, $term_id ) = explode( '|', $wert, 2 );
			$tax_obj = get_taxonomy( sanitize_key( $tax ) );
			if ( ! $tax_obj || ! $tax_obj->public ) continue;   // nur öffentliche Taxonomien
			$nach_tax[ $tax_obj->name ][] = absint( $term_id );
		}
		if ( $nach_tax ) {
			$tax_query = [ 'relation' => 'OR' ];
			foreach ( $nach_tax as $tax => $term_ids ) {
				$tax_query[] = [ 'taxonomy' => $tax, 'field' => 'term_id', 'terms' => $term_ids ];
			}
			$abfrage['tax_query'] = $tax_query;
		}

		switch ( $a['sortierung'] ?? 'datum_neu' ) {
			case 'datum_alt': $abfrage['orderby'] = 'date';       $abfrage['order'] = 'ASC';  break;
			case 'titel':     $abfrage['orderby'] = 'title';      $abfrage['order'] = 'ASC';  break;
			case 'menue':     $abfrage['orderby'] = 'menu_order'; $abfrage['order'] = 'ASC';  break;
			case 'zufall':    $abfrage['orderby'] = 'rand';                                   break;
			default:          $abfrage['orderby'] = 'date';       $abfrage['order'] = 'DESC';
		}
	}

	$karten = [];
	foreach ( get_posts( $abfrage ) as $beitrag ) {
		$karten[] = [
			'bild_id'   => get_post_thumbnail_id( $beitrag ),
			'bild_url'  => '',
			'titel'     => get_the_title( $beitrag ),
			'text'      => get_the_excerpt( $beitrag ),
			'url'       => get_permalink( $beitrag ),
			'neuer_tab' => false,
			'nofollow'  => false,
		];
	}
	return $karten;
}

/** Komplettes Slider-HTML als String. */
function mmk_showcase_slider_markup( array $a ) {
	$a = array_merge( [
		'karten'            => [],
		'titel_tag'         => 'h3',
		'text_tag'          => 'p',
		'bereich_label'     => 'Referenzen',
		'autoplay_sekunden' => 0,
		'pause_bei_hover'   => true,
		'punkte'            => true,
		'pfeile'            => false,
		'verlauf_hover_aus' => true,
		'pfeil_html'        => '',
		'style'             => '',
	], $a );

	if ( ! $a['karten'] ) return '';

	$erlaubte_tags = [ 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'div', 'span' ];
	$titel_tag  = in_array( $a['titel_tag'], $erlaubte_tags, true ) ? $a['titel_tag'] : 'h3';
	$text_tag   = in_array( $a['text_tag'], $erlaubte_tags, true ) ? $a['text_tag'] : 'p';
	$pfeil_html = $a['pfeil_html'] ?: mmk_showcase_slider_pfeil_standard();

	$klassen = [ 'mmk-showcase-slider' ];
	if ( $a['verlauf_hover_aus'] ) $klassen[] = 'verlauf-hover-aus';
	if ( $a['punkte'] )            $klassen[] = 'mit-punkten';
	if ( $a['pfeile'] )            $klassen[] = 'mit-pfeilen';

	ob_start();
	?>
	<div class="<?php echo esc_attr( implode( ' ', $klassen ) ); ?>" data-autoplay="<?php echo esc_attr( $a['autoplay_sekunden'] ); ?>" data-pause-hover="<?php echo $a['pause_bei_hover'] ? '1' : '0'; ?>"<?php echo $a['style'] ? ' style="' . esc_attr( $a['style'] ) . '"' : ''; ?>>
		<div class="mmk-showcase-slider__rahmen">
			<ul class="mmk-showcase-slider__track" aria-label="<?php echo esc_attr( $a['bereich_label'] ); ?>">
				<?php foreach ( $a['karten'] as $karte ) : ?>
				<li class="mmk-showcase-slider__karte">
					<?php
					$hat_link  = ! empty( $karte['url'] );
					$attribute = '';
					if ( ! empty( $karte['neuer_tab'] ) ) $attribute .= ' target="_blank"';
					$rel = trim( ( ! empty( $karte['neuer_tab'] ) ? 'noopener ' : '' ) . ( ! empty( $karte['nofollow'] ) ? 'nofollow' : '' ) );
					if ( $rel ) $attribute .= ' rel="' . esc_attr( $rel ) . '"';
					?>
					<<?php echo $hat_link ? 'a href="' . esc_url( $karte['url'] ) . '"' . $attribute : 'div'; ?> class="mmk-showcase-slider__link">
						<?php if ( ! empty( $karte['bild_id'] ) ) :
							echo wp_get_attachment_image( $karte['bild_id'], 'large', false, [ 'class' => 'mmk-showcase-slider__bild', 'loading' => 'lazy' ] );
						elseif ( ! empty( $karte['bild_url'] ) ) : ?>
							<img class="mmk-showcase-slider__bild" src="<?php echo esc_url( $karte['bild_url'] ); ?>" alt="" loading="lazy">
						<?php endif; ?>
						<span class="mmk-showcase-slider__verlauf" aria-hidden="true"></span>
						<span class="mmk-showcase-slider__inhalt">
							<?php if ( ! empty( $karte['titel'] ) ) : ?>
							<<?php echo $titel_tag; ?> class="mmk-showcase-slider__titel"><?php echo esc_html( $karte['titel'] ); ?></<?php echo $titel_tag; ?>>
							<?php endif; ?>
							<?php if ( ! empty( $karte['text'] ) ) : ?>
							<<?php echo $text_tag; ?> class="mmk-showcase-slider__text"><?php echo esc_html( $karte['text'] ); ?></<?php echo $text_tag; ?>>
							<?php endif; ?>
						</span>
						<?php if ( $hat_link ) : ?>
						<span class="mmk-showcase-slider__pfeil" aria-hidden="true"><?php echo $pfeil_html; ?></span>
						<?php endif; ?>
					</<?php echo $hat_link ? 'a' : 'div'; ?>>
				</li>
				<?php endforeach; ?>
			</ul>
		</div>

		<div class="mmk-showcase-slider__nav">
			<button type="button" class="mmk-showcase-slider__slider-pfeil zurueck" aria-label="Vorherige Karte">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 18l-6-6 6-6"/></svg>
			</button>
			<div class="mmk-showcase-slider__punkte" role="group" aria-label="Karten-Auswahl">
				<?php foreach ( $a['karten'] as $i => $karte ) : ?>
				<button type="button" class="mmk-showcase-slider__punkt" aria-label="<?php echo esc_attr( 'Karte ' . ( $i + 1 ) . ( ! empty( $karte['titel'] ) ? ': ' . $karte['titel'] : '' ) ); ?>"></button>
				<?php endforeach; ?>
			</div>
			<button type="button" class="mmk-showcase-slider__slider-pfeil weiter" aria-label="Nächste Karte">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6"/></svg>
			</button>
		</div>
	</div>
	<?php
	return ob_get_clean();
}
