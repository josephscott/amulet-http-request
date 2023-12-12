<?php
declare( strict_types = 1 );

test( 'get', function () {
	$http = new \Amulet\HTTP\Request();
	$response = $http->get( url: 'http://127.0.0.1:7878/' );

	$data = json_decode( $response['body'], associative: true );

	expect( $response['error'] )->toBe( false );
	expect( $response['response_code'] )->toBe( 200 );
	expect( $response['headers']['content-type'] )->toBe( 'application/json' );

	expect( $data['method'] )->toBe( 'get' );
} );

test( 'get: timeout', function () {
	$http = new \Amulet\HTTP\Request();
	$response = $http->get(
		url: 'http://127.0.0.1:7878/?sleep=1',
		options: [ 'timeout' => 1 ]
	);

	expect( $response['error'] )->toBe( true );
	expect( $response['response_code'] )->toBe( 0 );
} );

test( 'get: query vars', function () {
	$http = new \Amulet\HTTP\Request();
	$response = $http->get( url: 'http://127.0.0.1:7878/?hello=world' );

	$data = json_decode( $response['body'], associative: true );

	expect( $response['error'] )->toBe( false );
	expect( $response['response_code'] )->toBe( 200 );
	expect( $response['headers']['content-type'] )->toBe( 'application/json' );
	expect( $data['get']['hello'] )->toBe( 'world' );
} );

test( 'get: response timing', function () {
	$http = new \Amulet\HTTP\Request();
	$response = $http->get( url: 'http://127.0.0.1:7878/?hello=world' );

	expect( $response['error'] )->toBe( false );
	expect( $response['response_code'] )->toBe( 200 );
	expect( $response['headers']['content-type'] )->toBe( 'application/json' );

	expect( $response['timing']['dns'] )->toBeGreaterThan( 0 );
	expect( $response['timing']['tcp'] )->toBeGreaterThan( 0 );
	expect( $response['timing']['tls'] )->toBe( 0 );
	expect( $response['timing']['redirect'] )->toBe( 0 );
	expect( $response['timing']['http'] )->toBeGreaterThan( 0 );
	expect( $response['timing']['total'] )->toBeGreaterThan( 0 );
} );
