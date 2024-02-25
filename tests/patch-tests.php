<?php
declare( strict_types = 1 );

test( 'patch-curl: data', function () {
	$http = new \Amulet\HTTP\Request();
	$response = $http->patch(
		url: 'http://127.0.0.1:7878/?method=patch',
		data: [
			'this' => 'here',
			'hello' => 'world',
		]
	);

	$data = json_decode( $response->body, associative: true );

	expect( $response->error )->toBe( false );
	expect( $response->code )->toBe( 200 );
	expect( $response->headers['content-type'] )->toBe( 'application/json' );
} );

test( 'patch-php: data', function () {
	$http = new \Amulet\HTTP\Request();
	$http->default_options['using'] = 'php';
	$response = $http->patch(
		url: 'http://127.0.0.1:7878/?method=patch',
		data: [
			'this' => 'here',
			'hello' => 'world',
		]
	);

	$data = json_decode( $response->body, associative: true );

	expect( $response->error )->toBe( false );
	expect( $response->code )->toBe( 200 );
	expect( $response->headers['content-type'] )->toBe( 'application/json' );
} );
