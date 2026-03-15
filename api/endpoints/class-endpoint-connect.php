<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * POST /wp-json/clienvy-connect/v1/connect
 *
 * Validates the connection secret and confirms the connection works. Also synchronize settings.
 */
class Clienvy_Endpoint_Connect {

	public function __construct(
		private Clienvy_Auth $auth,
		private Clienvy_Settings_Synchronize $synchronize,
	) {}

	public function handle( WP_REST_Request $request ): WP_REST_Response {
		if ( ! $this->auth->is_authorized( $request ) ) {
			return $this->auth->unauthorized();
		}

		$body = $request->get_json_params() ?? [];

		if ( ! empty( $body ) ) {
			$this->synchronize->apply( $body );
		}

		return new WP_REST_Response(
			array_merge( [ 'success' => true, 'message' => 'Connection verified.' ], Clienvy_Site_Info::collect() ),
			200
		);
	}
}
