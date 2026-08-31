<?php
/**
 * Plugin bootstrap.
 *
 * @package Soderlind\KjeksGoogle
 */

declare(strict_types=1);

namespace Soderlind\KjeksGoogle;

/**
 * Wires the add-on's subsystems.
 */
final class Plugin {

	private static ?Plugin $instance = null;

	private bool $booted = false;

	public static function instance(): self {
		if ( ! self::$instance instanceof self ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function boot(): void {
		if ( $this->booted ) {
			return;
		}
		$this->booted = true;

		$settings = new Settings();
		$settings->hooks();

		( new ConsentMode( $settings ) )->hooks();
		( new NetworkDefaultsTab() )->hooks();
	}
}
