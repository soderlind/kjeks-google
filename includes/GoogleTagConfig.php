<?php
/**
 * Google tag configuration resolution.
 *
 * @package Soderlind\KjeksGoogle
 */

declare(strict_types=1);

namespace Soderlind\KjeksGoogle;

/**
 * Resolves the effective Google tag configuration from stored values.
 *
 * Pure input-to-output: it takes the network and per-site stored values and
 * returns the normalized, merged config. No WordPress options are read here, so
 * the resolution and id-validation rules are testable through one interface;
 * Settings is the thin adapter that reads options and renders admin screens.
 */
final class GoogleTagConfig {

	/**
	 * Allowed gating categories (never necessary).
	 *
	 * @return array<string, string>
	 */
	public static function categories(): array {
		return array(
			'analytics' => __( 'Analytics', 'kjeks-google' ),
			'marketing' => __( 'Marketing', 'kjeks-google' ),
		);
	}

	/**
	 * @return array{gtm_id: string, ga4_id: string, gating_category: string}
	 */
	public static function defaults(): array {
		return array(
			'gtm_id'          => '',
			'ga4_id'          => '',
			'gating_category' => 'analytics',
		);
	}

	/**
	 * Merges network defaults with per-site overrides into the effective config.
	 *
	 * @param array<string, mixed> $network Stored network values.
	 * @param array<string, mixed> $site    Stored per-site values.
	 * @return array{gtm_id: string, ga4_id: string, gating_category: string}
	 */
	public static function resolve( array $network, array $site ): array {
		$network = self::normalize( $network );
		$site    = array_filter( $site, static fn ( $v ): bool => '' !== $v && null !== $v );

		return self::normalize( array_merge( $network, $site ) );
	}

	/**
	 * Normalizes raw values against the defaults.
	 *
	 * @param array<string, mixed> $values Raw values.
	 * @return array{gtm_id: string, ga4_id: string, gating_category: string}
	 */
	public static function normalize( array $values ): array {
		$merged   = array_merge( self::defaults(), $values );
		$category = in_array( $merged['gating_category'], array_keys( self::categories() ), true )
			? (string) $merged['gating_category']
			: 'analytics';

		return array(
			'gtm_id'          => self::clean_id( (string) $merged['gtm_id'], 'GTM' ),
			'ga4_id'          => self::clean_id( (string) $merged['ga4_id'], 'G' ),
			'gating_category' => $category,
		);
	}

	/**
	 * Keeps only a valid Google container/measurement id.
	 */
	private static function clean_id( string $id, string $prefix ): string {
		$id = strtoupper( trim( $id ) );
		if ( '' === $id ) {
			return '';
		}

		return preg_match( '/^' . $prefix . '-[A-Z0-9]+$/', $id ) ? $id : '';
	}
}
