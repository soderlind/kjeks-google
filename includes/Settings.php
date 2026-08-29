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
	public const NETWORK_SLUG    = 'kjeks-google-network';

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
		// Priority 11: register after the core parent menu (priority 10) so the parent hookname resolves.
		add_action( 'admin_menu', array( $this, 'site_menu' ), 11 );
		add_action( 'admin_init', array( $this, 'register_site_settings' ) );
		add_action( 'network_admin_menu', array( $this, 'network_menu' ), 11 );
		add_action( 'admin_post_kjeks_google_save_network', array( $this, 'save_network' ) );
	}

	public function site_menu(): void {
		add_submenu_page(
			'kjeks-network',
			__( 'Kjeks Google', 'kjeks-google' ),
			__( 'Google', 'kjeks-google' ),
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

	public function network_menu(): void {
		add_submenu_page(
			'kjeks-network',
			__( 'Kjeks Google', 'kjeks-google' ),
			__( 'Google', 'kjeks-google' ),
			'manage_network_options',
			self::NETWORK_SLUG,
			array( $this, 'render_network_page' )
		);
	}

	public function render_network_page(): void {
		if ( ! current_user_can( 'manage_network_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'kjeks-google' ) );
		}

		$network = $this->network();
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Kjeks Google — network defaults', 'kjeks-google' ); ?></h1>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="kjeks_google_save_network" />
				<?php wp_nonce_field( 'kjeks_google_save_network' ); ?>
				<table class="form-table" role="presentation">
					<tr>
						<th><label for="kg-net-gtm"><?php esc_html_e( 'GTM container ID', 'kjeks-google' ); ?></label></th>
						<td><input class="regular-text" id="kg-net-gtm" name="gtm_id" type="text" value="<?php echo esc_attr( $network['gtm_id'] ); ?>" placeholder="GTM-XXXXXX" /></td>
					</tr>
					<tr>
						<th><label for="kg-net-ga4"><?php esc_html_e( 'GA4 measurement ID', 'kjeks-google' ); ?></label></th>
						<td><input class="regular-text" id="kg-net-ga4" name="ga4_id" type="text" value="<?php echo esc_attr( $network['ga4_id'] ); ?>" placeholder="G-XXXXXXX" /></td>
					</tr>
					<tr>
						<th><label for="kg-net-cat"><?php esc_html_e( 'Load container when this category is granted', 'kjeks-google' ); ?></label></th>
						<td><?php $this->category_select( 'kg-net-cat', 'gating_category', $network['gating_category'] ); ?></td>
					</tr>
				</table>
				<?php submit_button( __( 'Save network defaults', 'kjeks-google' ) ); ?>
			</form>
		</div>
		<?php
	}

	public function save_network(): void {
		if ( ! current_user_can( 'manage_network_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to do this.', 'kjeks-google' ) );
		}

		check_admin_referer( 'kjeks_google_save_network' );

		$values = GoogleTagConfig::normalize(
			array(
				'gtm_id'          => sanitize_text_field( wp_unslash( $_POST['gtm_id'] ?? '' ) ),
				'ga4_id'          => sanitize_text_field( wp_unslash( $_POST['ga4_id'] ?? '' ) ),
				'gating_category' => sanitize_key( wp_unslash( $_POST['gating_category'] ?? 'analytics' ) ),
			)
		);

		update_site_option( self::NETWORK_OPTION, $values );

		wp_safe_redirect( add_query_arg( 'updated', '1', network_admin_url( 'admin.php?page=' . self::NETWORK_SLUG ) ) );
		exit;
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
