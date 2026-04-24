<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AJAX handler for sending an SMTP test email from the settings page.
 */
class Clienvy_Settings_SMTP_Test {

	public function __construct() {
		add_action( 'wp_ajax_clienvy_send_test_email', [ $this, 'ajax_send_test_email' ] );
	}

	public function ajax_send_test_email(): void {
		Clienvy_Admin_Auth::check();

		$settings = get_option( 'clienvy_settings', [] );
		if ( empty( $settings['smtp_enabled'] ) ) {
			wp_send_json_error( [ 'message' => Clienvy_I18n::t( '_admin._endpoints._smtp.not_enabled' ) ] );
		}

		$to = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
		if ( ! is_email( $to ) ) {
			wp_send_json_error( [ 'message' => Clienvy_I18n::t( '_admin._endpoints._smtp.invalid_email' ) ] );
		}

		$wp_error     = null;
		$debug_lines  = [];
		$mask_counter = 0;

		$capture_error = function ( $error ) use ( &$wp_error ) {
			if ( is_wp_error( $error ) ) {
				$wp_error = $error;
			}
		};

		$attach_debug = function ( $phpmailer ) use ( &$debug_lines, &$mask_counter ) {
			$phpmailer->SMTPDebug   = 2;
			$phpmailer->Debugoutput = function ( $str, $level ) use ( &$debug_lines, &$mask_counter ) {
				$line = rtrim( (string) $str );
				if ( $line === '' ) {
					return;
				}

				if ( stripos( $line, 'CLIENT ->' ) !== false && preg_match( '/AUTH\s+(LOGIN|PLAIN|XOAUTH2)/i', $line ) ) {
					$mask_counter   = 2;
					$debug_lines[]  = $line;
					return;
				}
				if ( $mask_counter > 0 && stripos( $line, 'CLIENT ->' ) !== false ) {
					$debug_lines[] = 'CLIENT -> [credentials masked]';
					$mask_counter--;
					return;
				}

				$debug_lines[] = $line;
			};
		};

		add_action( 'wp_mail_failed', $capture_error );
		add_action( 'phpmailer_init', $attach_debug, 999 );

		$thrown = '';
		try {
			$sent = wp_mail(
				$to,
				Clienvy_I18n::t( '_admin._endpoints._smtp.test_subject' ),
				Clienvy_I18n::t( '_admin._endpoints._smtp.test_body' )
			);
		} catch ( \Throwable $e ) {
			$sent   = false;
			$thrown = $e->getMessage();
		}

		remove_action( 'wp_mail_failed', $capture_error );
		remove_action( 'phpmailer_init', $attach_debug, 999 );

		if ( $sent ) {
			wp_send_json_success( [ 'message' => Clienvy_I18n::t( '_admin._endpoints._smtp.sent_to', [ 'email' => $to ] ) ] );
		}

		$reasons = [];
		if ( $wp_error instanceof WP_Error ) {
			$wp_message = $wp_error->get_error_message();
			if ( $wp_message !== '' ) {
				$reasons[] = $wp_message;
			}
		}
		if ( $thrown !== '' ) {
			$reasons[] = $thrown;
		}

		$reason_text = $reasons ? implode( ' — ', array_unique( $reasons ) ) : '';
		$message     = $reason_text !== ''
			? Clienvy_I18n::t( '_admin._endpoints._smtp.send_failed_with_reason', [ 'reason' => $reason_text ] )
			: Clienvy_I18n::t( '_admin._endpoints._smtp.send_failed' );

		$details = [];

		$host     = trim( (string) ( $settings['smtp_host'] ?? '' ) );
		$port     = (int) ( $settings['smtp_port'] ?? 0 );
		$username = trim( (string) ( $settings['smtp_username'] ?? '' ) );

		if ( $host !== '' || $username !== '' ) {
			$details[] = [
				'variant' => 'heading',
				'message' => Clienvy_I18n::t( '_word.smtp_config_heading' ),
			];
			if ( $host !== '' ) {
				$details[] = [ 'message' => Clienvy_I18n::t( '_admin._endpoints._smtp.detail_server', [ 'host' => $host, 'port' => $port ] ) ];
			}
			if ( $username !== '' ) {
				$details[] = [ 'message' => Clienvy_I18n::t( '_admin._endpoints._smtp.detail_user', [ 'user' => $username ] ) ];
			}
		}

		if ( $reasons ) {
			$details[] = [
				'variant' => 'heading',
				'message' => Clienvy_I18n::t( '_word.smtp_reason_heading' ),
			];
			foreach ( array_unique( $reasons ) as $reason ) {
				$details[] = [ 'variant' => 'error', 'message' => $reason ];
			}
		}

		if ( $debug_lines ) {
			$details[] = [
				'variant' => 'heading',
				'message' => Clienvy_I18n::t( '_word.smtp_debug_heading' ),
			];
			foreach ( $debug_lines as $line ) {
				$details[] = [ 'message' => $line ];
			}
		}

		wp_send_json_error( [
			'message' => $message,
			'details' => $details,
		] );
	}
}
