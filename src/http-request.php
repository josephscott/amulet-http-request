<?php
declare( strict_types = 1 );

namespace Amulet;

class HTTP_Request {
	private string $url;

	public function __construct( string $url ) {
		$this->url = $url;
	}
}
