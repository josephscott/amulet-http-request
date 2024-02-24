<?php
declare( strict_types = 1 );

namespace Amulet\HTTP;

class Response {
	public bool $error = false;
	public int $response_code = 0;
	public float $http_version = 0.0;
	public array $headers = [];
	public array $timing = [];
	public string $body = '';

	public function __construct() {}
}
