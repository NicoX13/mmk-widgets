<?php
/**
 * Plugin Name:       mmk Widgets
 * Plugin URI:        https://github.com/NicoX13/mmk-widgets
 * Description:       Eigene Elementor-Widgets der Agentur modus. Erweiterbar: jedes Widget ist ein eigener Ordner unter widgets/.
 * Version:           0.1.0
 * Author:            Nico Felix
 * Author URI:        https://www.modus-media.de
 * Text Domain:       mmk-widgets
 * Requires PHP:      7.4
 * Requires at least: 6.0
 * Elementor tested up to: 4.2
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'MMK_WIDGETS_VERSION', '0.1.0' );
define( 'MMK_WIDGETS_FILE', __FILE__ );
define( 'MMK_WIDGETS_PATH', plugin_dir_path( __FILE__ ) );
define( 'MMK_WIDGETS_URL', plugin_dir_url( __FILE__ ) );

require MMK_WIDGETS_PATH . 'includes/plugin.php';

MMK_Widgets::start();
