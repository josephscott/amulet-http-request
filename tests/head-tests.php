<?php
declare( strict_types = 1 );

test( 'head-curl', function () {
	$http = new \Amulet\HTTP\Request();
	$response = $http->get( url: 'http://127.0.0.1:7878/' );

	expect( $response->error )->toBe( false );
	expect( $response->code )->toBe( 200 );
	expect( $response->headers['content-type'] )->toBe( 'application/json' );
} );

test( 'head-php', function () {
	$http = new \Amulet\HTTP\Request();
	$http->default_options['using'] = 'php';
	$response = $http->get( url: 'http://127.0.0.1:7878/' );

	expect( $response->error )->toBe( false );
	expect( $response->code )->toBe( 200 );
	expect( $response->headers['content-type'] )->toBe( 'application/json' );
} );
