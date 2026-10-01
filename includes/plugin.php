<?php
/**
 * mmk Widgets — Bootstrap.
 * Registriert die Elementor-Kategorie "modus Widgets", findet alle Widgets
 * automatisch (jeder Ordner unter widgets/ mit einer widget.php) und meldet
 * deren style.css/script.js bei WordPress an. Neues Widget = neuer Ordner,
 * Klassenname nach Muster: ordner-name → MMK_Widget_Ordner_Name.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

final class MMK_Widgets {

	private static $instanz = null;

	public static function start() {
		if ( null === self::$instanz ) {
			self::$instanz = new self();
		}
		return self::$instanz;
	}

	private function __construct() {
		add_action( 'plugins_loaded', [ $this, 'pruefe_elementor' ] );
		add_action( 'init', [ $this, 'lade_module' ] );
		add_action( 'elementor/elements/categories_registered', [ $this, 'kategorie' ] );
		add_action( 'elementor/widgets/register', [ $this, 'registriere_widgets' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'registriere_assets' ] );
		$this->updates();
	}

	/** Ohne Elementor gibt es nur einen Hinweis im Admin, keinen Fehler. */
	public function pruefe_elementor() {
		if ( did_action( 'elementor/loaded' ) ) return;
		add_action( 'admin_notices', function () {
			echo '<div class="notice notice-warning"><p><strong>mmk Widgets:</strong> Elementor ist nicht aktiv — die Widgets fehlen im Elementor-Panel, die Shortcodes (z. B. <code>[mmk_showcase_slider]</code>) funktionieren weiterhin.</p></div>';
		} );
	}

	/** Elementor-unabhängige Teile jedes Widgets laden (Ausgabe + Shortcode). */
	public function lade_module() {
		foreach ( glob( MMK_WIDGETS_PATH . 'widgets/*', GLOB_ONLYDIR ) as $ordner ) {
			foreach ( [ 'render.php', 'shortcode.php' ] as $teil ) {
				if ( file_exists( $ordner . '/' . $teil ) ) require_once $ordner . '/' . $teil;
			}
		}
	}

	public function kategorie( $elements_manager ) {
		$elements_manager->add_category( 'mmk', [
			'title' => 'modus Widgets',
			'icon'  => 'eicon-apps',
		] );
	}

	/** Alle Widget-Ordner durchgehen und die Klassen bei Elementor anmelden. */
	public function registriere_widgets( $widgets_manager ) {
		foreach ( glob( MMK_WIDGETS_PATH . 'widgets/*/widget.php' ) as $datei ) {
			require_once $datei;
			$klasse = 'MMK_Widget_' . str_replace( '-', '_', ucwords( basename( dirname( $datei ) ), '-' ) );
			if ( class_exists( $klasse ) ) {
				$widgets_manager->register( new $klasse() );
			}
		}
	}

	/**
	 * CSS/JS je Widget nur anmelden — geladen wird es von Elementor erst,
	 * wenn das Widget wirklich auf der Seite liegt (get_style_depends).
	 * Dateidatum als Version = sicheres Cache-Busting.
	 */
	public function registriere_assets() {
		foreach ( glob( MMK_WIDGETS_PATH . 'widgets/*', GLOB_ONLYDIR ) as $ordner ) {
			$name = 'mmk-' . basename( $ordner );
			$css  = $ordner . '/style.css';
			$js   = $ordner . '/script.js';
			if ( file_exists( $css ) ) {
				wp_register_style( $name, MMK_WIDGETS_URL . 'widgets/' . basename( $ordner ) . '/style.css', [], filemtime( $css ) );
			}
			if ( file_exists( $js ) ) {
				wp_register_script( $name, MMK_WIDGETS_URL . 'widgets/' . basename( $ordner ) . '/script.js', [], filemtime( $js ), true );
			}
		}
	}

	/**
	 * Updates über GitHub: prüft das Repo auf neue Versionen (Tag/Release)
	 * und bietet sie wie ein normales Plugin-Update in WordPress an.
	 * Privates Repo? Dann in wp-config.php einen Token hinterlegen:
	 * define( 'MMK_WIDGETS_GITHUB_TOKEN', '...' );
	 */
	private function updates() {
		$puc = MMK_WIDGETS_PATH . 'includes/lib/plugin-update-checker/plugin-update-checker.php';
		if ( ! file_exists( $puc ) ) return;
		require_once $puc;
		$checker = YahnisElsts\PluginUpdateChecker\v5\PucFactory::buildUpdateChecker(
			'https://github.com/NicoX13/mmk-widgets/',
			MMK_WIDGETS_FILE,
			'mmk-widgets'
		);
		$checker->setBranch( 'main' );
		if ( defined( 'MMK_WIDGETS_GITHUB_TOKEN' ) ) {
			$checker->setAuthentication( MMK_WIDGETS_GITHUB_TOKEN );
		}
	}
}
