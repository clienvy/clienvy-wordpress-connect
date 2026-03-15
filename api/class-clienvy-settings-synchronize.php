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
	];

	private const BOOL_FIELDS = [
		'custom_login_styling_enabled',
		'customer_access',
	];

	private const CSS_FIELDS = [
		'custom_login_css',
	];

	/**
	 * Merges the provided body fields into the stored settings.
	 * Only fields that are present in the body are updated.
	 * Returns true if the admin slug changed (caller may want to flush rewrite rules).
	 */
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

		update_option( 'clienvy_settings', $current );

		return ( $current['custom_login_slug'] ?? '' ) !== $old_slug;
	}
}
