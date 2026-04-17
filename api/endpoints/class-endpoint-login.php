<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GET /wp-json/clienvy-connect/v1/login?token=xxx
 *
 * Resolves a one-time SSO token, logs the user in, and redirects to wp-admin.
 * No authentication required — the token itself is the credential.
 * Token is invalidated immediately after use.
 */
class Clienvy_Endpoint_Login {

	public function handle( WP_REST_Request $request ): void {
		$token = sanitize_text_field( $request->get_param( 'token' ) );

		if ( ! $token ) {
			wp_die( 'Token missing.', 'Clienvy Connect', [ 'response' => 400 ] );
		}

		$data = get_transient( 'clienvy_sso_' . $token );

		if ( ! $data ) {
			wp_die( 'Token expired or invalid.', 'Clienvy Connect', [ 'response' => 401 ] );
		}

		// Invalidate immediately — one-time use
		delete_transient( 'clienvy_sso_' . $token );

		$user = get_user_by( 'id', $data['user_id'] );
		if ( ! $user ) {
			wp_die( 'User not found.', 'Clienvy Connect', [ 'response' => 404 ] );
		}

		wp_set_current_user( $user->ID );
		wp_set_auth_cookie( $user->ID, false );
		do_action( 'wp_login', $user->user_login, $user );

		wp_safe_redirect( admin_url() );
		exit;
	}
}
