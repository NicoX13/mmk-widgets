<?php
/**
 * mmk Tab-Slider — gemeinsame Ausgabe (Elementor-Widget UND Shortcode).
 * Großes Panel: links Headline, rechts Titel + Beschreibung + optionaler
 * Button, unten Tab-Leiste, seitlich Pfeile, optional Punkte darunter.
 * Folien wechseln per Tab/Pfeil/Punkt oder automatisch (Autoplay).
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/** Eingebaute Pfeile (Fallback ohne gewähltes Icon). */
function mmk_tab_slider_pfeil_standard( $richtung ) {
	$pfad = 'zurueck' === $richtung ? 'M15 18l-6-6 6-6' : 'M9 6l6 6-6 6';
	return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="' . $pfad . '"/></svg>';
}

/**
 * Sicherheit: Farbwert für das style-Attribut absichern. Erlaubt nur
 * Zeichen, die in CSS-Farben vorkommen — kein Semikolon, keine URLs,
 * keine zusätzlichen Deklarationen.
 */
function mmk_tab_slider_farbe( $wert ) {
	$wert = trim( (string) $wert );
	return preg_match( '/\A[#a-zA-Z0-9(),.%\s\-\/]{1,64}\z/', $wert ) ? $wert : '';
}

/** Komplettes Tab-Slider-HTML als String. */
function mmk_tab_slider_markup( array $a ) {
	$a = array_merge( [
		'headline'          => 'Unsere Leistungen im Überblick:',
		'headline_tag'      => 'h2',
		'titel_tag'         => 'h3',
		'text_tag'          => 'p',
		'folien'            => [],
		'bereich_label'     => 'Unsere Leistungen',
		'autoplay_sekunden' => 0,
		'pause_bei_hover'   => true,
		'pfeile'            => true,
		'punkte'            => false,
		'pfeil_html'        => '',
		'style'             => '',
	], $a );

	if ( ! $a['folien'] ) return '';

	$erlaubte_tags = [ 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'div', 'span' ];
	$headline_tag = in_array( $a['headline_tag'], $erlaubte_tags, true ) ? $a['headline_tag'] : 'h2';
	$titel_tag    = in_array( $a['titel_tag'], $erlaubte_tags, true ) ? $a['titel_tag'] : 'h3';
	$text_tag     = in_array( $a['text_tag'], $erlaubte_tags, true ) ? $a['text_tag'] : 'p';
	$id           = wp_unique_id( 'mmk-ts-' );

	$klassen = [ 'mmk-tab-slider' ];
	if ( $a['pfeile'] ) $klassen[] = 'mit-pfeilen';
	if ( $a['punkte'] ) $klassen[] = 'mit-punkten';

	ob_start();
	?>
	<div class="<?php echo esc_attr( implode( ' ', $klassen ) ); ?>" data-autoplay="<?php echo esc_attr( $a['autoplay_sekunden'] ); ?>" data-pause-hover="<?php echo $a['pause_bei_hover'] ? '1' : '0'; ?>"<?php echo $a['style'] ? ' style="' . esc_attr( $a['style'] ) . '"' : ''; ?>>
		<div class="mmk-tab-slider__buehne">
			<div class="mmk-tab-slider__folien">
				<?php foreach ( $a['folien'] as $i => $folie ) :
					$bg = mmk_tab_slider_farbe( $folie['bg'] ?? '' );
				?>
				<div class="mmk-tab-slider__folie<?php echo 0 === $i ? ' ist-aktiv' : ''; ?>"
					id="<?php echo esc_attr( "$id-folie-$i" ); ?>"
					role="tabpanel"
					aria-labelledby="<?php echo esc_attr( "$id-tab-$i" ); ?>"
					<?php echo $bg ? 'style="--tts-folie-bg:' . esc_attr( $bg ) . ';"' : ''; ?>>
					<<?php echo $headline_tag; ?> class="mmk-tab-slider__headline"><?php echo esc_html( ! empty( $folie['headline'] ) ? $folie['headline'] : $a['headline'] ); ?></<?php echo $headline_tag; ?>>
					<div class="mmk-tab-slider__inhalt">
						<?php if ( ! empty( $folie['titel'] ) ) : ?>
						<<?php echo $titel_tag; ?> class="mmk-tab-slider__titel"><?php echo esc_html( $folie['titel'] ); ?></<?php echo $titel_tag; ?>>
						<?php endif; ?>
						<?php if ( ! empty( $folie['text'] ) ) : ?>
						<<?php echo $text_tag; ?> class="mmk-tab-slider__text"><?php echo nl2br( esc_html( $folie['text'] ) ); ?></<?php echo $text_tag; ?>>
						<?php endif; ?>
						<?php if ( ! empty( $folie['button_text'] ) && ! empty( $folie['button_url'] ) ) :
							$rel = trim( ( ! empty( $folie['neuer_tab'] ) ? 'noopener ' : '' ) . ( ! empty( $folie['nofollow'] ) ? 'nofollow' : '' ) );
						?>
						<a class="mmk-tab-slider__button" href="<?php echo esc_url( $folie['button_url'] ); ?>"<?php echo ! empty( $folie['neuer_tab'] ) ? ' target="_blank"' : ''; ?><?php echo $rel ? ' rel="' . esc_attr( $rel ) . '"' : ''; ?>><?php echo esc_html( $folie['button_text'] ); ?></a>
						<?php endif; ?>
					</div>
				</div>
				<?php endforeach; ?>
			</div>

			<button type="button" class="mmk-tab-slider__pfeil zurueck" aria-label="Vorherige Folie">
				<?php echo $a['pfeil_html'] ? '<span class="ist-gespiegelt">' . $a['pfeil_html'] . '</span>' : mmk_tab_slider_pfeil_standard( 'zurueck' ); ?>
			</button>
			<button type="button" class="mmk-tab-slider__pfeil weiter" aria-label="Nächste Folie">
				<?php echo $a['pfeil_html'] ?: mmk_tab_slider_pfeil_standard( 'weiter' ); ?>
			</button>

			<div class="mmk-tab-slider__tabs" role="tablist" aria-label="<?php echo esc_attr( $a['bereich_label'] ); ?>">
				<?php foreach ( $a['folien'] as $i => $folie ) : ?>
				<button type="button" role="tab" class="mmk-tab-slider__tab"
					id="<?php echo esc_attr( "$id-tab-$i" ); ?>"
					aria-controls="<?php echo esc_attr( "$id-folie-$i" ); ?>"
					aria-selected="<?php echo 0 === $i ? 'true' : 'false'; ?>"
					tabindex="<?php echo 0 === $i ? '0' : '-1'; ?>"><?php echo esc_html( $folie['titel'] ?? 'Folie ' . ( $i + 1 ) ); ?></button>
				<?php endforeach; ?>
			</div>
		</div>

		<div class="mmk-tab-slider__punkte" role="group" aria-label="Folien-Auswahl">
			<?php foreach ( $a['folien'] as $i => $folie ) : ?>
			<button type="button" class="mmk-tab-slider__punkt" aria-label="<?php echo esc_attr( 'Folie ' . ( $i + 1 ) . ( ! empty( $folie['titel'] ) ? ': ' . $folie['titel'] : '' ) ); ?>"<?php echo 0 === $i ? ' aria-current="true"' : ''; ?>></button>
			<?php endforeach; ?>
		</div>
	</div>
	<?php
	return ob_get_clean();
}
