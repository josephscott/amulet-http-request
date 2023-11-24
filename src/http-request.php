<?php
declare( strict_types = 1 );

namespace Amulet;

class HTTP_Request {
	public function __construct() {}

	public function get( string $url, array $headers = [] ) : array {
		$out = $this->request( 'GET', $url, $headers );
		return $out;
	}

	public function request(
		string $method,
		string $url,
		array $headers = [],
		array $data = []
	) : array {
		$out = [
			'error' => false,
			'response_code' => 0,
			'http_version' => 0,
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
			CURLOPT_HTTPHEADER => $headers,
			CURLOPT_HEADERFUNCTION => function ( $curl, $header ) use ( &$out ) {
				$length = strlen( $header );
				$parts = explode( ':', $header, 2 );

				if ( count( $parts ) < 2 ) {
					if ( preg_match( '/^HTTP\/([0-9\.]+)\s+([0-9]+)/', $header, $matches ) ) {
						$out['http_version'] = (int) $matches[1];
						$out['response_code'] = (int) $matches[2];
						return $length;
					} else {
						// Invalid header
						return $length;
					}
				}

				$key = strtolower( trim( $parts[0] ) );
				$value = trim( $parts[1] );
				if ( is_numeric( $value ) ) {
					$value = (int) $value;
				}

				// May need to reconsider this for duplicate headers
				$out['headers'][$key] = $value;
				return $length;
			},
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
