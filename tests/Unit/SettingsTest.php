<?php
/**
 * Settings adapter tests — WordPress option reads mocked with Brain Monkey.
 *
 * @package Soderlind\KjeksGoogle
 */

declare(strict_types=1);

use Brain\Monkey\Functions;
use Soderlind\KjeksGoogle\GoogleTagConfig;
use Soderlind\KjeksGoogle\Settings;

it( 'reads and normalizes the network option', function (): void {
	Functions\when( 'get_site_option' )->justReturn(
		array(
			'ga4_id'          => 'g-net',
			'gating_category' => 'marketing',
		)
	);

	$network = ( new Settings() )->network();

	expect( $network['ga4_id'] )->toBe( 'G-NET' )
		->and( $network['gating_category'] )->toBe( 'marketing' )
		->and( $network['gtm_id'] )->toBe( '' );
} );

it( 'returns only the non-empty per-site overrides', function (): void {
	Functions\when( 'get_option' )->justReturn(
		array(
			'gtm_id'          => 'GTM-SITE',
			'ga4_id'          => '',
			'gating_category' => null,
		)
	);

	expect( ( new Settings() )->site_overrides() )->toBe( array( 'gtm_id' => 'GTM-SITE' ) );
} );

it( 'tolerates a corrupted (non-array) stored option', function (): void {
	Functions\when( 'get_site_option' )->justReturn( 'corrupted' );

	expect( ( new Settings() )->network() )->toBe( GoogleTagConfig::defaults() );
} );
