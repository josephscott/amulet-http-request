<?php
declare( strict_types = 1 );

test( 'delete', function () {
	// curl
	$http = new \Amulet\HTTP\Request();
	$response = $http->delete( url: 'http://127.0.0.1:7878/?method=delete' );

	$data = json_decode( $response['body'], associative: true );

	expect( $response['error'] )->toBe( false );
	expect( $response['response_code'] )->toBe( 200 );
	expect( $response['headers']['content-type'] )->toBe( 'application/json' );

	// php
	$http = new \Amulet\HTTP\Request();
	$http->default_options['using'] = 'php';
	$response = $http->delete( url: 'http://127.0.0.1:7878/?method=delete' );

	$data = json_decode( $response['body'], associative: true );

	expect( $response['error'] )->toBe( false );
	expect( $response['response_code'] )->toBe( 200 );
	expect( $response['headers']['content-type'] )->toBe( 'application/json' );
} );
