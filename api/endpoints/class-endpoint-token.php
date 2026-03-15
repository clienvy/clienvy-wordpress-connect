<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * POST /wp-json/clienvy-connect/v1/token
 *
 * Creates a one-time SSO token for a given user (identified by email).
 * If no user exists with that email, one is created.
 * Token expires after 5 minutes and can only be used once.
 */
class Clienvy_Endpoint_Token {

	public function __construct( private Clienvy_Auth $auth ) {}

	public function handle( WP_REST_Request $request ): WP_REST_Response {
		if ( ! $this->auth->is_authorized( $request ) ) {
			return $this->auth->unauthorized();
		}

		$body = $request->get_json_params();

		foreach ( [ 'first_name', 'last_name', 'email', 'role' ] as $field ) {
			if ( empty( $body[ $field ] ) ) {
				return new WP_REST_Response( [
					'success' => false,
					'message' => "Required field is missing: {$field}",
				], 400 );
			}
		}

		$email      = sanitize_email( $body['email'] );
		$first_name = sanitize_text_field( $body['first_name'] );
		$last_name  = sanitize_text_field( $body['last_name'] );
		$role       = sanitize_text_field( $body['role'] );

		if ( ! is_email( $email ) ) {
			return new WP_REST_Response( [ 'success' => false, 'message' => 'Invalid email.' ], 400 );
		}

		$user = get_user_by( 'email', $email ) ?: $this->create_user( $email, $first_name, $last_name, $role );

		if ( is_wp_error( $user ) ) {
			return new WP_REST_Response( [
				'success' => false,
				'message' => 'Could not create user: ' . $user->get_error_message(),
			], 500 );
		}

		$token = bin2hex( random_bytes( 32 ) );

		set_transient( 'clienvy_sso_' . $token, [ 'user_id' => $user->ID ], 5 * MINUTE_IN_SECONDS );

		return new WP_REST_Response( [
			'success'    => true,
			'token'      => $token,
			'login_url'  => rest_url( 'clienvy-connect/v1/login' ) . '?token=' . $token,
			'expires_in' => 300,
		], 200 );
	}

	private function create_user( string $email, string $first_name, string $last_name, string $role ): WP_User|WP_Error {
		$base     = sanitize_user( strtolower( $first_name . '.' . $last_name ), true );
		$username = $base;
		$i        = 1;

		while ( username_exists( $username ) ) {
			$username = $base . $i++;
		}

		$user_id = wp_create_user( $username, wp_generate_password( 24 ), $email );

		if ( is_wp_error( $user_id ) ) {
			return $user_id;
		}

		wp_update_user( [
			'ID'         => $user_id,
			'first_name' => $first_name,
			'last_name'  => $last_name,
			'role'       => $role,
		] );

		return get_user_by( 'id', $user_id );
	}
}
