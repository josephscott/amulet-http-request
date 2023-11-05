<?php
declare( strict_types = 1 );

namespace Amulet;

class HTTP_Request {
	public function __construct() {}

	/**
	 * @return array<mixed>
	 */
	public function get( string $url ) : array {
		$out = $this->request( 'GET', $url );
		return $out;
	}

	/**
	 * @param array<string> $headers
	 * @param array<mixed> $data
	 * @return array<mixed>
	 */
	public function request(
		string $method,
		string $url,
		array $headers = [],
		array $data = []
	) : array {
		$out = [
			'error' => false,
			'headers' => [],
			'body' => '',
			'timing' => [],
		];

		$curl = curl_init( $url );
		if ( $curl === false ) {
			$out['error'] = true;
			return $out;
		}

		curl_setopt_array( $curl, [
			CURLOPT_CUSTOMREQUEST => $method,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_FOLLOWLOCATION => false,
			CURLOPT_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
		] );
		$response = curl_exec( $curl );
		if ( $response === false ) {
			$out['error'] = true;
		} else {
			$out['body'] = $response;
		}

		curl_close( $curl );

		return $out;
	}
}
