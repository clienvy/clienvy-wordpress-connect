<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Shared nonce and capability check for admin-ajax handlers.
 */
class Clienvy_Admin_Auth {

	public const NONCE_ACTION = 'clienvy_admin_nonce';
	public const NONCE_FIELD  = 'nonce';
	public const CAPABILITY   = 'manage_options';

	public static function check(): void {
		check_ajax_referer( self::NONCE_ACTION, self::NONCE_FIELD );

		if ( ! current_user_can( self::CAPABILITY ) ) {
			wp_send_json_error( Clienvy_I18n::t( '_admin._endpoints._auth.unauthorized' ), 403 );
		}
	}
}
