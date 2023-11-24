<?php
declare( strict_types = 1 );

test( 'get', function () {
	$http = new \Amulet\HTTP_Request();
	$response = $http->get( url: 'http://127.0.0.1:7878/' );

	expect( $response['error'] )->toBe( false );
	expect( $response['response_code'] )->toBe( 200 );
	expect( $response['headers']['content-type'] )->toBe( 'application/json' );
} );

test( 'get: timeout', function () {
	$http = new \Amulet\HTTP_Request();
	$http->timeout = 1;
	$response = $http->get( url: 'http://127.0.0.1:7878/?sleep=1' );

	expect( $response['error'] )->toBe( true );
	expect( $response['response_code'] )->toBe( 0 );
} );
