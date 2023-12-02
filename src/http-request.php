<?php
declare( strict_types = 1 );

namespace Amulet;

class HTTP_Request {
	public int $timeout = 10;

	public string $encoding = 'gzip';

	public function __construct() {}

	public function delete( string $url, array $headers = [] ) : array {
		$out = $this->request( 'DELETE', $url, $headers );
		return $out;
	}

	public function get( string $url, array $headers = [] ) : array {
		$out = $this->request( 'GET', $url, $headers );
		return $out;
	}

	public function head( string $url, array $headers = [] ) : array {
		$out = $this->request( 'HEAD', $url, $headers );
		return $out;
	}

	public function options( string $url, array $headers = [] ) : array {
		$out = $this->request( 'OPTIONS', $url, $headers );
		return $out;
	}

	public function patch(
		string $url,
		array $headers = [],
		array $data = [],
	) : array {
		$out = $this->request( 'PATCH', $url, $headers, $data );
		return $out;
	}

	public function post(
		string $url,
		array $headers = [],
		array $data = [],
	) : array {
		$out = $this->request( 'POST', $url, $headers, $data );
		return $out;
	}

	public function put(
		string $url,
		array $headers = [],
		array $data = [],
	) : array {
		$out = $this->request( 'PUT', $url, $headers, $data );
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
			\CURLOPT_CUSTOMREQUEST => $method,
			\CURLOPT_RETURNTRANSFER => true,
			\CURLOPT_FOLLOWLOCATION => false,
			\CURLOPT_TIMEOUT => $this->timeout,
			\CURLOPT_PROTOCOLS => \CURLPROTO_HTTP | \CURLPROTO_HTTPS,
			\CURLOPT_HTTPHEADER => $headers,
			\CURLOPT_HEADERFUNCTION => function ( $curl, $header ) use ( &$out ) {
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

		if ( $method === 'HEAD' ) {
			curl_setopt( $curl, \CURLOPT_NOBODY, true );
		}

		if ( $method === 'POST' ) {
			curl_setopt( $curl, \CURLOPT_POST, true );
			curl_setopt( $curl, \CURLOPT_POSTFIELDS, $data );
		}

		if ( $method === 'PUT' ) {
			curl_setopt( $curl, \CURLOPT_POSTFIELDS, $data );
		}

		if ( ! empty( $this->encoding ) ) {
			curl_setopt( $curl, \CURLOPT_ENCODING, $this->encoding );
		}

		$response = curl_exec( $curl );
		if ( $response === false ) {
			$out['error'] = true;
		} else {
			$out['body'] = $response;
		}

		$info = [
			'dns' => curl_getinfo( $curl, \CURLINFO_NAMELOOKUP_TIME_T ),
			'tcp' => curl_getinfo( $curl, \CURLINFO_CONNECT_TIME_T ),
			'tls' => curl_getinfo( $curl, \CURLINFO_APPCONNECT_TIME_T ),
			'redirect' => curl_getinfo( $curl, \CURLINFO_REDIRECT_TIME_T ),
			'http' => curl_getinfo( $curl, \CURLINFO_STARTTRANSFER_TIME_T ),
			'total' => curl_getinfo( $curl, \CURLINFO_TOTAL_TIME_T ),
		];

		$out['timing']['dns'] = $info['dns'];

		$out['timing']['tcp'] = $info['tcp'];
		$out['timing']['tcp'] -= $out['timing']['dns'];

		$out['timing']['tls'] = 0;
		if ( $info['tls'] > 0 ) {
			$out['timing']['tls'] = $info['tls'] - $out['timing']['tcp'];
		}

		$out['timing']['redirect'] = 0;
		if ( $info['redirect'] > 0 ) {
			if ( $out['timing']['tls'] > 0 ) {
				$out['timing']['redirect'] = $info['redirect'] - $out['timing']['tls'];
			} else {
				$out['timing']['redirect'] = $info['redirect'] - $out['timing']['tcp'];
			}
		}

		$http = $info['http'];
		if ( $out['timing']['redirect'] > 0 ) {
			$out['timing']['http'] = $info['http'] - $out['timing']['redirect'];
		} elseif ( $out['timing']['tls'] > 0 ) {
			$out['timing']['http'] = $info['http'] - $out['timing']['tls'];
		} else {
			$out['timing']['http'] = $info['http'] - $out['timing']['tcp'];
		}

		$out['timing']['total'] = $info['total'];

		// Move from microseconds to milliseconds
		foreach ( $out['timing'] as $k => $v ) {
			if ( $v > 0 ) {
				$out['timing'][$k] = $v / 1000;
			}
		}

		curl_close( $curl );
		return $out;
	}
}
