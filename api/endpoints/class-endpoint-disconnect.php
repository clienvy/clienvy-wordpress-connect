<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * POST /wp-json/clienvy-connect/v1/disconnect
 *
 * Disconnects this WordPress site from Clienvy:
 *  - Removes all pushed settings (styling, portal URLs, etc.)
 *  - Rotates the connection secret so the old key is no longer valid
 *  - Marks the new secret as unrevealed so the admin must reveal it in the settings page before reconnecting
 *
 * Requires a valid connection secret in the request.
 */
class Clienvy_Endpoint_Disconnect {

	public function __construct( private Clienvy_Auth $auth ) {}

	public function handle( WP_REST_Request $request ): WP_REST_Response {
		if ( ! $this->auth->is_authorized( $request ) ) {
			return $this->auth->unauthorized();
		}

		// Wipe all CRM-pushed settings
		delete_option( 'clienvy_settings' );

		// Rotate the secret so the old CRM key stops working immediately
		update_option( 'clienvy_connection_secret', clienvy_generate_secret() );
		update_option( 'clienvy_secret_revealed', false );

		// Flush rewrite rules in case a custom login slug was active
		flush_rewrite_rules();

		return new WP_REST_Response( [
			'success' => true,
			'message' => 'Site disconnected from Clienvy.',
		], 200 );
	}
}
