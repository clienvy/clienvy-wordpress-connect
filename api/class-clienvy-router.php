<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers all REST API routes for Clienvy Connect.
 *
 * All routes live under the fixed namespace clienvy-connect/v1
 */
class Clienvy_Router {

	private const NAMESPACE = 'clienvy-connect/v1';

	public function __construct() {
		add_action( 'rest_api_init', [ $this, 'register' ] );
	}

	public function register(): void {
		$auth = new Clienvy_Auth();
		$sync = new Clienvy_Settings_Synchronize();

		$routes = [
			[ 'POST', '/connect',     new Clienvy_Endpoint_Connect( $auth, $sync ) ],
			[ 'POST', '/synchronize', new Clienvy_Endpoint_Synchronize( $auth, $sync ) ],
			[ 'POST', '/get-token',       new Clienvy_Endpoint_Token( $auth ) ],
			[ 'POST', '/disconnect',  new Clienvy_Endpoint_Disconnect( $auth ) ],
			[ 'GET',  '/login',       new Clienvy_Endpoint_Login() ],
		];

		foreach ( $routes as [ $method, $path, $endpoint ] ) {
			register_rest_route( self::NAMESPACE, $path, [
				'methods'             => $method,
				'callback'            => [ $endpoint, 'handle' ],
				'permission_callback' => '__return_true',
			] );
		}
	}
}
