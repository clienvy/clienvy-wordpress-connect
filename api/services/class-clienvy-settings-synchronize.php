<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Shared service for applying settings pushed from Clienvy.
 *
 * Used by both the /connect and /synchronize endpoints
 */
class Clienvy_Settings_Synchronize {

	private const TEXT_FIELDS = [
		'employee_portal_url',
		'customer_portal_url',
		'website_id',
		'website_application_id',
		'organization_name',
		'primary_color',
		'custom_login_slug',
		'logo_url',
		'smtp_host',
		'smtp_port',
		'smtp_username',
		'smtp_sender_name',
		'smtp_sender_email',
		'smtp_reply_to_name',
		'smtp_reply_to_email',
	];

	private const BOOL_FIELDS = [
		'custom_login_styling_enabled',
		'customer_access',
		'smtp_enabled',
		'smtp_reply_to_enabled',
	];

	private const CSS_FIELDS = [
		'custom_login_css',
	];

	private const RAW_FIELDS = [
		'smtp_password',
	];

	public function apply( array $body ): bool {
		$current  = get_option( 'clienvy_settings', [] );
		$old_slug = $current['custom_login_slug'] ?? '';

		foreach ( self::TEXT_FIELDS as $field ) {
			if ( array_key_exists( $field, $body ) ) {
				$current[ $field ] = sanitize_text_field( $body[ $field ] );
			}
		}

		foreach ( self::BOOL_FIELDS as $field ) {
			if ( array_key_exists( $field, $body ) ) {
				$current[ $field ] = (bool) $body[ $field ];
			}
		}

		foreach ( self::CSS_FIELDS as $field ) {
			if ( array_key_exists( $field, $body ) ) {
				$current[ $field ] = wp_strip_all_tags( $body[ $field ] );
			}
		}

		foreach ( self::RAW_FIELDS as $field ) {
			if ( array_key_exists( $field, $body ) ) {
				$current[ $field ] = is_string( $body[ $field ] ) ? $body[ $field ] : '';
			}
		}

		update_option( 'clienvy_settings', $current );

		return ( $current['custom_login_slug'] ?? '' ) !== $old_slug;
	}
}
