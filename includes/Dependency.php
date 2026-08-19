<?php
/**
 * Kjeks dependency detection.
 *
 * @package Soderlind\KjeksGoogle
 */

declare(strict_types=1);

namespace Soderlind\KjeksGoogle;

/**
 * Detects whether the Kjeks consent layer is available.
 */
final class Dependency {

	/**
	 * Whether the Kjeks public API is loaded.
	 */
	public static function kjeks_active(): bool {
		return function_exists( 'kjeks_register_integration' ) && function_exists( 'kjeks_is_granted' );
	}

	/**
	 * Prints an admin notice when Kjeks is missing.
	 */
	public static function admin_notice(): void {
		if ( self::kjeks_active() ) {
			return;
		}

		printf(
			'<div class="notice notice-error"><p>%s</p></div>',
			esc_html__( 'Kjeks Google requires the Kjeks plugin to be active.', 'kjeks-google' )
		);
	}
}
