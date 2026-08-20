<?php
/**
 * GoogleTagConfig resolution and id-validation tests.
 *
 * @package Soderlind\KjeksGoogle
 */

declare(strict_types=1);

use Soderlind\KjeksGoogle\GoogleTagConfig;

it( 'defaults to empty ids and the analytics gate', function (): void {
	expect( GoogleTagConfig::defaults() )->toBe(
		array(
			'gtm_id'          => '',
			'ga4_id'          => '',
			'gating_category' => 'analytics',
		)
	);
} );

it( 'exposes only analytics and marketing as gating categories', function (): void {
	expect( array_keys( GoogleTagConfig::categories() ) )->toBe( array( 'analytics', 'marketing' ) );
} );

it( 'keeps well-formed GTM and GA4 ids', function (): void {
	$config = GoogleTagConfig::normalize(
		array(
			'gtm_id'          => 'GTM-ABC123',
			'ga4_id'          => 'G-XYZ789',
			'gating_category' => 'marketing',
		)
	);

	expect( $config['gtm_id'] )->toBe( 'GTM-ABC123' )
		->and( $config['ga4_id'] )->toBe( 'G-XYZ789' )
		->and( $config['gating_category'] )->toBe( 'marketing' );
} );

it( 'upper-cases and trims ids', function (): void {
	$config = GoogleTagConfig::normalize(
		array(
			'gtm_id' => '  gtm-abc  ',
			'ga4_id' => 'g-xyz',
		)
	);

	expect( $config['gtm_id'] )->toBe( 'GTM-ABC' )
		->and( $config['ga4_id'] )->toBe( 'G-XYZ' );
} );

it( 'clears malformed ids', function (): void {
	$config = GoogleTagConfig::normalize(
		array(
			'gtm_id' => 'UA-123',
			'ga4_id' => 'not-an-id!',
		)
	);

	expect( $config['gtm_id'] )->toBe( '' )
		->and( $config['ga4_id'] )->toBe( '' );
} );

it( 'falls back to analytics for an unknown gating category', function (): void {
	expect( GoogleTagConfig::normalize( array( 'gating_category' => 'necessary' ) )['gating_category'] )->toBe( 'analytics' )
		->and( GoogleTagConfig::normalize( array( 'gating_category' => '' ) )['gating_category'] )->toBe( 'analytics' );
} );

it( 'lets non-empty per-site values override the network defaults', function (): void {
	$network = array(
		'gtm_id'          => 'GTM-NET111',
		'ga4_id'          => 'G-NET111',
		'gating_category' => 'analytics',
	);
	$site = array(
		'gtm_id'          => '',        // empty → keep the network value
		'ga4_id'          => 'G-SITE222',
		'gating_category' => 'marketing',
	);

	$config = GoogleTagConfig::resolve( $network, $site );

	expect( $config['gtm_id'] )->toBe( 'GTM-NET111' )
		->and( $config['ga4_id'] )->toBe( 'G-SITE222' )
		->and( $config['gating_category'] )->toBe( 'marketing' );
} );
