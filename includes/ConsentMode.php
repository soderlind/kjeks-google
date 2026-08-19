<?php
/**
 * Google Consent Mode v2 wiring.
 *
 * @package Soderlind\KjeksGoogle
 */

declare(strict_types=1);

namespace Soderlind\KjeksGoogle;

/**
 * Emits Consent Mode v2 defaults, gates the Google container behind Kjeks
 * consent, and syncs consent signals when the visitor changes their choice.
 *
 * Two layers of protection (see Kjeks ADR / grilling Q7):
 *  1. Consent Mode defaults are set to `denied` before anything Google loads.
 *  2. The container itself is withheld until the gating category is granted,
 *     so Google cannot bypass the Kjeks consent layer.
 */
final class ConsentMode {

	public function __construct( private readonly Settings $settings ) {}

	public function hooks(): void {
		add_action( 'wp_head', array( $this, 'print_default' ), 1 );
		add_action( 'wp_head', array( $this, 'print_sync' ), 2 );
		add_action( 'kjeks_register_integrations', array( $this, 'register_container' ) );
	}

	private function is_configured(): bool {
		if ( ! Dependency::kjeks_active() ) {
			return false;
		}
		$config = $this->settings->resolve();

		return '' !== $config['gtm_id'] || '' !== $config['ga4_id'];
	}

	/**
	 * Consent Mode v2 defaults — everything denied, before any Google code.
	 */
	public function print_default(): void {
		if ( ! $this->is_configured() ) {
			return;
		}

		$script = 'window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}'
			. "gtag('consent','default',{"
			. "'ad_storage':'denied','ad_user_data':'denied','ad_personalization':'denied',"
			. "'analytics_storage':'denied','wait_for_update':500});";

		$this->print_inline( $script );
	}

	/**
	 * Syncs Consent Mode signals from Kjeks consent, now and on every change.
	 */
	public function print_sync(): void {
		if ( ! $this->is_configured() ) {
			return;
		}

		$script = '(function(){function g(c){return window.kjeks?window.kjeks.isGranted(c):false;}'
			. "function apply(){if(typeof gtag!=='function'){return;}"
			. "gtag('consent','update',{"
			. "'analytics_storage':g('analytics')?'granted':'denied',"
			. "'ad_storage':g('marketing')?'granted':'denied',"
			. "'ad_user_data':g('marketing')?'granted':'denied',"
			. "'ad_personalization':g('marketing')?'granted':'denied'});}"
			. "window.addEventListener('kjeks:granted',apply);"
			. "window.addEventListener('kjeks:withdrawn',apply);"
			. "document.addEventListener('DOMContentLoaded',apply);})();";

		$this->print_inline( $script );
	}

	/**
	 * Registers the Google container with Kjeks so it stays inert until consent.
	 */
	public function register_container(): void {
		if ( ! $this->is_configured() ) {
			return;
		}

		$config      = $this->settings->resolve();
		$src_scripts = array();
		$inline      = array();

		if ( '' !== $config['ga4_id'] ) {
			$src_scripts[] = 'https://www.googletagmanager.com/gtag/js?id=' . rawurlencode( $config['ga4_id'] );
			$inline[]      = sprintf( "gtag('js', new Date()); gtag('config', '%s');", esc_js( $config['ga4_id'] ) );
		}

		if ( '' !== $config['gtm_id'] ) {
			$src_scripts[] = 'https://www.googletagmanager.com/gtm.js?id=' . rawurlencode( $config['gtm_id'] );
		}

		kjeks_register_integration(
			'google-tags',
			array(
				'category'    => $config['gating_category'],
				'label'       => __( 'Google Tag Manager / Analytics', 'kjeks-google' ),
				'src_scripts' => $src_scripts,
				'inline'      => $inline,
			)
		);
	}

	private function print_inline( string $script ): void {
		// Developer-controlled JavaScript; ids are validated in Settings.
		printf( "<script>%s</script>\n", $script ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped, WordPress.WP.EnqueuedResources.NonEnqueuedScript
	}
}
