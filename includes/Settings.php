<?php
/**
 * Settings storage, resolution, and admin screens.
 *
 * @package Soderlind\KjeksGoogle
 */

declare(strict_types=1);

namespace Soderlind\KjeksGoogle;

/**
 * Network-default + per-site Google tag configuration — admin adapter.
 *
 * Reads options and renders the admin screens; resolution and id validation
 * live in GoogleTagConfig.
 */
final class Settings {

	private const NETWORK_OPTION = 'kjeks_google_network';
	private const SITE_OPTION    = 'kjeks_google';
	public const SITE_SLUG       = 'kjeks-google';

	/**
	 * Allowed gating categories (never necessary).
	 *
	 * @return array<string, string>
	 */
	public static function categories(): array {
		return GoogleTagConfig::categories();
	}

	/**
	 * Network defaults.
	 *
	 * @return array{gtm_id: string, ga4_id: string, gating_category: string}
	 */
	public function network(): array {
		$stored = get_site_option( self::NETWORK_OPTION, array() );

		return GoogleTagConfig::normalize( is_array( $stored ) ? $stored : array() );
	}

	/**
	 * Per-site overrides (only the fields the site changed).
	 *
	 * @return array<string, string>
	 */
	public function site_overrides(): array {
		$stored = get_option( self::SITE_OPTION, array() );

		return is_array( $stored ) ? array_filter( $stored, static fn ( $v ): bool => '' !== $v && null !== $v ) : array();
	}

	/**
	 * Effective configuration for the current site.
	 *
	 * @return array{gtm_id: string, ga4_id: string, gating_category: string}
	 */
	public function resolve(): array {
		$network = get_site_option( self::NETWORK_OPTION, array() );
		$config  = GoogleTagConfig::resolve(
			is_array( $network ) ? $network : array(),
			$this->site_overrides()
		);

		/**
		 * Filters the resolved Google tag configuration.
		 *
		 * @param array{gtm_id: string, ga4_id: string, gating_category: string} $config  Resolved config.
		 * @param int                                                            $blog_id Current blog id.
		 */
		return (array) apply_filters( 'kjeks_google_config', $config, get_current_blog_id() );
	}

	// Admin screens.

	public function hooks(): void {
		// The network defaults live on the core "Cookie Consent" tab shell (see
		// NetworkDefaultsTab). This adapter only exposes the per-site override
		// screen, and only on Multisite where per-site overrides apply.
		if ( ! is_multisite() ) {
			return;
		}

		add_action( 'admin_menu', array( $this, 'site_menu' ) );
		add_action( 'admin_init', array( $this, 'register_site_settings' ) );
	}

	public function site_menu(): void {
		add_options_page(
			__( 'Google (Kjeks)', 'kjeks-google' ),
			__( 'Google (Kjeks)', 'kjeks-google' ),
			'manage_options',
			self::SITE_SLUG,
			array( $this, 'render_site_page' )
		);
	}

	public function register_site_settings(): void {
		register_setting(
			'kjeks_google_site',
			self::SITE_OPTION,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize_site' ),
				'default'           => array(),
			)
		);
	}

	/**
	 * @param mixed $input Raw submitted values.
	 * @return array<string, string>
	 */
	public function sanitize_site( mixed $input ): array {
		$input = is_array( $input ) ? $input : array();

		return GoogleTagConfig::normalize( $input );
	}

	public function render_site_page(): void {
		$network   = $this->network();
		$overrides = $this->site_overrides();
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Kjeks Google', 'kjeks-google' ); ?></h1>
			<p class="description"><?php esc_html_e( 'Leave a field blank to inherit the network default.', 'kjeks-google' ); ?></p>
			<form method="post" action="options.php">
				<?php settings_fields( 'kjeks_google_site' ); ?>
				<table class="form-table" role="presentation">
					<tr>
						<th><label for="kg-gtm"><?php esc_html_e( 'GTM container ID', 'kjeks-google' ); ?></label></th>
						<td>
							<input class="regular-text" id="kg-gtm" name="<?php echo esc_attr( self::SITE_OPTION ); ?>[gtm_id]" type="text" value="<?php echo esc_attr( $overrides['gtm_id'] ?? '' ); ?>" placeholder="<?php echo esc_attr( $network['gtm_id'] ); ?>" />
							<p class="description">GTM-XXXXXX</p>
						</td>
					</tr>
					<tr>
						<th><label for="kg-ga4"><?php esc_html_e( 'GA4 measurement ID', 'kjeks-google' ); ?></label></th>
						<td>
							<input class="regular-text" id="kg-ga4" name="<?php echo esc_attr( self::SITE_OPTION ); ?>[ga4_id]" type="text" value="<?php echo esc_attr( $overrides['ga4_id'] ?? '' ); ?>" placeholder="<?php echo esc_attr( $network['ga4_id'] ); ?>" />
							<p class="description">G-XXXXXXX</p>
						</td>
					</tr>
					<tr>
						<th><label for="kg-cat"><?php esc_html_e( 'Load container when this category is granted', 'kjeks-google' ); ?></label></th>
						<td><?php $this->category_select( 'kg-cat', self::SITE_OPTION . '[gating_category]', $overrides['gating_category'] ?? $network['gating_category'] ); ?></td>
					</tr>
				</table>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}

	private function category_select( string $id, string $name, string $current ): void {
		echo '<select id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '">';
		foreach ( self::categories() as $slug => $label ) {
			printf(
				'<option value="%s"%s>%s</option>',
				esc_attr( $slug ),
				selected( $slug, $current, false ),
				esc_html( $label )
			);
		}
		echo '</select>';
	}
}
