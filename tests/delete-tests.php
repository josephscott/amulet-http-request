<?php
declare( strict_types = 1 );

test( 'delete', function () {
	$http = new \Amulet\HTTP_Request();
	$response = $http->delete( url: 'http://127.0.0.1:7878/?method=delete' );

	$data = json_decode( $response['body'], associative: true );

	expect( $response['error'] )->toBe( false );
	expect( $response['response_code'] )->toBe( 200 );
	expect( $response['headers']['content-type'] )->toBe( 'application/json' );
} );
