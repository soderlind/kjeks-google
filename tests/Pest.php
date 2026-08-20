<?php
/**
 * Pest bootstrap: Brain Monkey lifecycle and shared WordPress stubs.
 *
 * @package Soderlind\KjeksGoogle
 */

declare(strict_types=1);

use Brain\Monkey;
use Brain\Monkey\Functions;

uses()
	->beforeEach(
		function (): void {
			Monkey\setUp();
			Functions\when( '__' )->returnArg( 1 );
		}
	)
	->afterEach(
		function (): void {
			Monkey\tearDown();
		}
	)
	->in( 'Unit' );
