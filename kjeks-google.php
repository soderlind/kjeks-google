<?php
/**
 * Plugin Name:       Kjeks Google
 * Plugin URI:        https://github.com/soderlind/kjeks-google
 * Description:       Google Tag Manager and Google Analytics 4 for Kjeks, using Consent Mode v2. Signals default to denied and the container is withheld until consent is granted through the Kjeks consent layer.
 * Version:           0.2.0
 * Requires at least: 6.8
 * Requires PHP:      8.3
 * Requires Plugins:  kjeks
 * Author:            Per Søderlind
 * Author URI:        https://soderlind.no
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       kjeks-google
 * Domain Path:       /languages
 * Network:           true
 *
 * @package Soderlind\KjeksGoogle
 */

declare(strict_types=1);

namespace Soderlind\KjeksGoogle;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'KJEKS_GOOGLE_VERSION', '0.1.0' );
define( 'KJEKS_GOOGLE_FILE', __FILE__ );
define( 'KJEKS_GOOGLE_DIR', plugin_dir_path( __FILE__ ) );
define( 'KJEKS_GOOGLE_URL', plugin_dir_url( __FILE__ ) );

require_once KJEKS_GOOGLE_DIR . 'includes/Dependency.php';
require_once KJEKS_GOOGLE_DIR . 'includes/GoogleTagConfig.php';
require_once KJEKS_GOOGLE_DIR . 'includes/Settings.php';
require_once KJEKS_GOOGLE_DIR . 'includes/ConsentMode.php';
require_once KJEKS_GOOGLE_DIR . 'includes/Plugin.php';

add_action(
	'plugins_loaded',
	static function (): void {
		load_plugin_textdomain( 'kjeks-google', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
		Plugin::instance()->boot();
	}
);
