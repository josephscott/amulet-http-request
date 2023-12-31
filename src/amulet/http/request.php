<?php
declare( strict_types = 1 );

namespace Amulet\HTTP;

class Request {
	public array $default_options = [
		'using' => 'curl',
		'timeout' => 30,
		'encoding' => 'gzip',
	];
	public array $default_headers = [
		'Connection' => 'close',
		'Accept' => '*/*',
		'User-Agent' => 'amulet-http-request',
	];

	public function __construct() {}

	public function delete(
		string $url,
		array $headers = [],
		array $options = []
	) : array {
		$out = $this->request(
			method: 'DELETE',
			url: $url,
			headers: $headers,
			options: $options
		);
		return $out;
	}

	public function get(
		string $url,
		array $headers = [],
		array $options = []
	) : array {
		$out = $this->request(
			method: 'GET',
			url: $url,
			headers: $headers,
			options: $options
		);
		return $out;
	}

	public function head(
		string $url,
		array $headers = [],
		array $options = []
	) : array {
		$out = $this->request(
			method: 'HEAD',
			url: $url,
			headers: $headers,
			options: $options
		);
		return $out;
	}

	public function options(
		string $url,
		array $headers = [],
		array $options = []
	) : array {
		$out = $this->request(
			method: 'OPTIONS',
			url: $url,
			headers: $headers,
			options: $options
		);
		return $out;
	}

	public function patch(
		string $url,
		array $headers = [],
		array $data = [],
		array $options = []
	) : array {
		$out = $this->request(
			method: 'PATCH',
			url: $url,
			headers: $headers,
			data: $data,
			options: $options
		);
		return $out;
	}

	public function post(
		string $url,
		array $headers = [],
		array $data = [],
		array $options = []
	) : array {
		$out = $this->request(
			method: 'POST',
			url: $url,
			headers: $headers,
			data: $data,
			options: $options
		);
		return $out;
	}

	public function put(
		string $url,
		array $headers = [],
		array $data = [],
		array $options = []
	) : array {
		$out = $this->request(
			method: 'PUT',
			url: $url,
			headers: $headers,
			data: $data,
			options: $options
		);
		return $out;
	}

	public function request(
		string $method,
		string $url,
		array $headers = [],
		array $data = [],
		array $options = []
	) : array {
		$out = [];

		$merged_options = array_merge( $this->default_options, $options );

		if ( $merged_options['using'] === 'curl' ) {
			$out = $this->request_curl(
				method: $method,
				url: $url,
				headers: $headers,
				data: $data,
				options: $merged_options
			);
		} elseif ( $merged_options['using'] === 'php' ) {
			$out = $this->request_php(
				method: $method,
				url: $url,
				headers: $headers,
				data: $data,
				options: $merged_options
			);
		}

		return $out;
	}

	public function request_curl(
		string $method,
		string $url,
		array $headers = [],
		array $data = [],
		array $options = []
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
			\CURLOPT_TIMEOUT => $options['timeout'],
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
			curl_setopt( $curl, \CURLOPT_POSTFIELDS, http_build_query( $data ) );
			$headers['Content-Type'] = 'application/x-www-form-urlencoded';
		}

		if ( $method === 'PUT' ) {
			curl_setopt( $curl, \CURLOPT_POSTFIELDS, http_build_query( $data ) );
			$headers['Content-Type'] = 'application/x-www-form-urlencoded';
		}

		if ( ! empty( $options['encoding'] ) ) {
			curl_setopt( $curl, \CURLOPT_ENCODING, $options['encoding'] );
		}

		$headers = array_merge( $this->default_headers, $headers );
		$curl_headers = [];
		foreach ( $headers as $k => $v ) {
			$curl_headers[] = "$k: $v";
		}
		curl_setopt( $curl, CURLOPT_HTTPHEADER, $curl_headers );

		$response = curl_exec( $curl );
		if ( $response === false ) {
			$out['error'] = true;
		} else {
			$out['body'] = $response;
		}

		$info = curl_getinfo( $curl );
		curl_close( $curl );

		$time = [];

		// PHPStan does not understand these yet
		// https://github.com/phpstan/phpstan-src/pull/2844

		// @phpstan-ignore-next-line
		$time['dns'] = $info['namelookup_time_us'];

		// @phpstan-ignore-next-line
		$time['tcp'] = $info['connect_time_us'];
		$time['tcp'] -= $time['dns'];

		// @phpstan-ignore-next-line
		$time['tls'] = $info['appconnect_time_us'];
		if ( $time['tls'] > 0 ) {
			$time['tls'] -= $time['tcp'];
		}

		// @phpstan-ignore-next-line
		$time['redirect'] = $info['redirect_time_us'];

		// @phpstan-ignore-next-line
		$time['ttfb'] = $info['starttransfer_time_us'];
		// @phpstan-ignore-next-line
		$time['ttfb'] -= $info['appconnect_time_us'];
		$time['ttfb'] += $time['redirect'];

		// @phpstan-ignore-next-line
		$time['done'] = $info['total_time_us'];

		$out['timing'] = $time;
		return $out;
	}

	public function request_php(
		string $method,
		string $url,
		array $headers = [],
		array $data = [],
		array $options = []
	) : array {
		$out = [
			'error' => false,
			'response_code' => 0,
			'http_version' => 0,
			'headers' => [],
			'body' => '',
			'timing' => [],
		];

		if (
			! empty( $options['encoding'] )
			&& function_exists( 'gzdecode' )
		) {
			$headers['Accept-Encoding'] = 'gzip';
		}

		$context = $this->php_build_context(
			method: $method,
			headers: $headers,
			data: $data,
			options: $options
		);

		// XXX: HACK
		// Make Pest happy by suppressing the warnings that can happen
		// I'd like to find a way to deal with warnings without using @
		$start = microtime( true );
		$body = @file_get_contents(
			filename: $url,
			use_include_path: false,
			context: $context
		);
		$out['done'] = number_format(
			( microtime( true ) - $start ),
			6
		);
		if ( $body === false ) {
			$out['error'] = true;
			return $out;
		}

		$out['body'] = $body;
		$out['headers'] = self::php_parse_headers(
			headers: $http_response_header
		);

		if (
			isset( $out['headers']['content-encoding'] )
			&& $out['headers']['content-encoding'] === 'gzip'
		) {
			$out['body'] = gzdecode( $body );
		}

		$out['response_code'] = $out['headers']['response_code'];
		unset( $out['headers']['response_code'] );

		if (
			$out['response_code'] < 200
			|| $out['response_code'] > 299
		) {
			$out['error'] = true;
			return $out;
		}

		return $out;
	}

	private function php_build_context(
		string $method,
		array $headers = [],
		array $data = [],
		array $options = []
	) {
		$php_options = [];
		$php_options['http'] = [];
		$php_options['http']['method'] = $method;
		$php_options['http']['timeout'] = $options['timeout'];

		$headers = array_merge( $this->default_headers, $headers );

		if ( ! empty( $data ) ) {
			$php_options['http']['content'] = http_build_query( $data );
			$headers['Content-Type'] = 'application/x-www-form-urlencoded';
		}

		foreach ( $headers as $header_name => $header_value ) {
			if ( ! isset( $php_options['http']['header'] ) ) {
				$php_options['http']['header'] = '';
			}

			$php_options['http']['header'] .= "$header_name: $header_value\r\n";
		}

		$context = stream_context_create( $php_options );
		return $context;
	}

	private function php_parse_headers( array $headers ):array {
		$parsed = [];

		$response_code = array_shift( $headers );
		if ( preg_match( '#HTTP/[0-9\.]+\s+([0-9]+)#', $response_code, $matches ) ) {
			$headers[] = 'response_code: ' . intval( $matches[1] );
		}

		foreach ( $headers as $header ) {
			$parts = explode( ':', $header, 2 );
			if ( count( $parts ) === 2 ) {
				$parts[1] = trim( $parts[1] );
				if ( is_numeric( $parts[1] ) ) {
					$parts[1] = (int) $parts[1];
				}

				$parsed[strtolower( trim( $parts[0] ) )] = $parts[1];
			}
		}

		return $parsed;
	}
}
