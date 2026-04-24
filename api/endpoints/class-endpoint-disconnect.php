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

	private $auth;

	public function __construct( Clienvy_Auth $auth ) {
		$this->auth = $auth;
	}

	public function handle( WP_REST_Request $request ): WP_REST_Response {
		if ( ! $this->auth->is_authorized( $request ) ) {
			return $this->auth->unauthorized();
		}

		delete_option( 'clienvy_settings' );

		update_option( 'clienvy_connection_secret', Clienvy_Secret::generate() );
		update_option( 'clienvy_secret_revealed', false );

		flush_rewrite_rules();

		return new WP_REST_Response( [
			'success' => true,
			'message' => Clienvy_I18n::t( '_api.connection_disconnect_done' ),
		], 200 );
	}
}
