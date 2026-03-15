<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles authentication for all API endpoints.
 */
class Clienvy_Auth {

	public function is_authorized( WP_REST_Request $request ): bool {
		$secret = get_option( 'clienvy_connection_secret' );

		if ( ! $secret ) {
			return false;
		}

		$authSecret = $request->get_header( 'Authorization' );
		if ( $authSecret ) {
			return hash_equals( $secret, $authSecret );
		}

		return false;
	}

	public function unauthorized(): WP_REST_Response {
		return new WP_REST_Response( [
			'success' => false,
			'message' => 'Invalid connection secret.',
		], 401 );
	}
}
