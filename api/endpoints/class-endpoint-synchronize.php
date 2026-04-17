<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * POST /wp-json/clienvy-connect/v1/synchronize
 *
 * Pushes styling and configuration settings from Clienvy to WordPress.
 * Returns environment info so Clienvy can keep its records up to date.
 */
class Clienvy_Endpoint_Synchronize {

	private $auth;
	private $synchronize;

	public function __construct( Clienvy_Auth $auth, Clienvy_Settings_Synchronize $synchronize ) {
		$this->auth        = $auth;
		$this->synchronize = $synchronize;
	}

	public function handle( WP_REST_Request $request ): WP_REST_Response {
		if ( ! $this->auth->is_authorized( $request ) ) {
			return $this->auth->unauthorized();
		}

		$body = $request->get_json_params() ?? [];
		$this->synchronize->apply( $body );

		return new WP_REST_Response(
			array_merge( [ 'success' => true, 'message' => 'Settings synchronized.' ], Clienvy_Site_Info::collect() ),
			200
		);
	}
}
