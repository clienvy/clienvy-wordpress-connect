<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Clienvy_Settings {

	public function __construct() {
		new Clienvy_Settings_Secret();
		new Clienvy_Settings_SMTP_Test();

		add_action( 'admin_menu', [ $this, 'add_menu' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
	}

	public function add_menu(): void {
		$connected = ! empty( get_option( 'clienvy_settings', [] )['website_id'] );
		$dot_color = $connected ? '#22c55e' : '#ef4444';
		$dot = ' <span style="display:inline-block;width:7px;height:7px;border-radius:50%;background:' . $dot_color . ';margin-left:5px;vertical-align:middle;position:relative;top:-1px;"></span>';

		add_options_page(
			'Clienvy Connect',
			'Clienvy Connect' . $dot,
			'manage_options',
			'clienvy-connect',
			[ $this, 'render_page' ]
		);
	}

	public function enqueue_scripts( string $hook ): void {
		if ( $hook !== 'settings_page_clienvy-connect' ) {
			return;
		}

		$dist_url = CLIENVY_PLUGIN_URL . 'assets/dist/';

		wp_enqueue_style( 'clienvy-admin', $dist_url . 'admin.css', [], CLIENVY_VERSION );
		wp_add_inline_style(
			'clienvy-admin',
			"@font-face { font-family: 'Circular Std'; font-weight: 300; src: url('" . esc_url( CLIENVY_PLUGIN_URL . 'assets/fonts/circular-std-300.woff' ) . "') format('woff'); }"
			. "@font-face { font-family: 'Circular Std'; font-weight: 400; src: url('" . esc_url( CLIENVY_PLUGIN_URL . 'assets/fonts/circular-std-400.woff' ) . "') format('woff'); }"
		);
		wp_enqueue_script( 'clienvy-admin', $dist_url . 'admin.js', [], CLIENVY_VERSION, true );

		$settings  = get_option( 'clienvy_settings', [] );
		$connected = ! empty( $settings['website_id'] );
		$user      = wp_get_current_user();

		wp_localize_script( 'clienvy-admin', 'clienvyAdmin', [
			'ajaxUrl'          => admin_url( 'admin-ajax.php' ),
			'nonce'            => wp_create_nonce( Clienvy_Admin_Auth::NONCE_ACTION ),
			'version'          => CLIENVY_VERSION,
			'connected'        => $connected,
			'orgName'          => $settings['organization_name'] ?? '',
			'secretRevealed'   => (bool) get_option( 'clienvy_secret_revealed', false ),
			'currentUserEmail' => $user->user_email ?? '',
			'smtp'             => [
				'enabled'        => ! empty( $settings['smtp_enabled'] ),
				'host'           => $settings['smtp_host'] ?? '',
				'port'           => $settings['smtp_port'] ?? '',
				'username'       => $settings['smtp_username'] ?? '',
				'senderName'     => $settings['smtp_sender_name'] ?? '',
				'senderEmail'    => $settings['smtp_sender_email'] ?? '',
				'replyToEnabled' => ! empty( $settings['smtp_reply_to_enabled'] ),
				'replyToName'    => $settings['smtp_reply_to_name'] ?? '',
				'replyToEmail'   => $settings['smtp_reply_to_email'] ?? '',
			],
		] );
	}

	public function render_page(): void {
		echo '<div id="clienvy-app" class="clienvy-admin"></div>';
	}
}
