<?php
/**
 * Network-default Google tag configuration, rendered as a core settings tab.
 *
 * @package Soderlind\KjeksGoogle
 */

declare(strict_types=1);

namespace Soderlind\KjeksGoogle;

use Soderlind\Kjeks\AddonKit\AbstractFormTab;

/**
 * A "Google" tab on the core "Cookie Consent" screen where the (network) admin
 * sets the default GTM container, GA4 id, and gating category. On Multisite,
 * individual sites can override these via {@see Settings} under their own
 * Settings menu; on a single site this tab is the only surface.
 */
final class NetworkDefaultsTab extends AbstractFormTab {

	protected function get_tab_slug(): string {
		return 'google';
	}

	protected function get_tab_label(): string {
		return __( 'Google', 'kjeks-google' );
	}

	protected function get_tab_intro(): string {
		return __( 'Set the network-wide Google Tag Manager and GA4 defaults. Containers load only after the visitor grants the selected consent category. Individual sites can override these values.', 'kjeks-google' );
	}

	protected function option_key(): string {
		return 'kjeks_google_network';
	}

	/**
	 * @return array{gtm_id: string, ga4_id: string, gating_category: string}
	 */
	protected function defaults(): array {
		return GoogleTagConfig::defaults();
	}

	/**
	 * @param array<string, mixed> $raw Raw values (submitted or stored).
	 * @return array{gtm_id: string, ga4_id: string, gating_category: string}
	 */
	protected function normalize( array $raw ): array {
		return GoogleTagConfig::normalize( $raw );
	}

	/**
	 * @param string                                                         $prefix Field-name prefix (always '').
	 * @param array{gtm_id: string, ga4_id: string, gating_category: string} $config Effective config to pre-fill.
	 */
	protected function render_fields( string $prefix, array $config ): void {
		?>
		<table class="form-table" role="presentation">
			<tr>
				<th><label for="kg-net-gtm"><?php esc_html_e( 'GTM container ID', 'kjeks-google' ); ?></label></th>
				<td><input class="regular-text" id="kg-net-gtm" name="<?php echo esc_attr( $this->field_name( $prefix, 'gtm_id' ) ); ?>" type="text" value="<?php echo esc_attr( $config['gtm_id'] ); ?>" placeholder="GTM-XXXXXX" /></td>
			</tr>
			<tr>
				<th><label for="kg-net-ga4"><?php esc_html_e( 'GA4 measurement ID', 'kjeks-google' ); ?></label></th>
				<td><input class="regular-text" id="kg-net-ga4" name="<?php echo esc_attr( $this->field_name( $prefix, 'ga4_id' ) ); ?>" type="text" value="<?php echo esc_attr( $config['ga4_id'] ); ?>" placeholder="G-XXXXXXX" /></td>
			</tr>
			<tr>
				<th><label for="kg-net-cat"><?php esc_html_e( 'Load container when this category is granted', 'kjeks-google' ); ?></label></th>
				<td><?php $this->category_select( 'kg-net-cat', $this->field_name( $prefix, 'gating_category' ), $config['gating_category'] ); ?></td>
			</tr>
		</table>
		<?php
	}

	private function category_select( string $id, string $name, string $current ): void {
		echo '<select id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '">';
		foreach ( GoogleTagConfig::categories() as $slug => $label ) {
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
