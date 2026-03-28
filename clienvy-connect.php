<?php
/**
 * Plugin Name: Clienvy Connect
 * Plugin URI: https://clienvy.io
 * Description: Connect WordPress with Clienvy
 * Version: 1.0.3
 * Author: Clienvy
 * License: GPL-2.0+
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'CLIENVY_VERSION', '1.0.3' );
define( 'CLIENVY_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'CLIENVY_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'CLIENVY_GITHUB_REPO', 'clienvy/clienvy-wordpress-connect' );

// ── API ───────────────────────────────────────────────────────────────────────
require_once CLIENVY_PLUGIN_DIR . 'api/class-clienvy-auth.php';
require_once CLIENVY_PLUGIN_DIR . 'api/class-clienvy-settings-synchronize.php';
require_once CLIENVY_PLUGIN_DIR . 'api/class-clienvy-site-info.php';
require_once CLIENVY_PLUGIN_DIR . 'api/endpoints/class-endpoint-connect.php';
require_once CLIENVY_PLUGIN_DIR . 'api/endpoints/class-endpoint-synchronize.php';
require_once CLIENVY_PLUGIN_DIR . 'api/endpoints/class-endpoint-token.php';
require_once CLIENVY_PLUGIN_DIR . 'api/endpoints/class-endpoint-login.php';
require_once CLIENVY_PLUGIN_DIR . 'api/endpoints/class-endpoint-disconnect.php';
require_once CLIENVY_PLUGIN_DIR . 'api/class-clienvy-router.php';

// ── Admin ─────────────────────────────────────────────────────────────────────
require_once CLIENVY_PLUGIN_DIR . 'admin/class-clienvy-updater.php';
require_once CLIENVY_PLUGIN_DIR . 'admin/class-clienvy-settings-page.php';

// ── Frontend ──────────────────────────────────────────────────────────────────
require_once CLIENVY_PLUGIN_DIR . 'frontend/class-clienvy-login-page.php';

// ── Font ──────────────────────────────────────────────────────────────────────
add_action( 'login_head', 'clienvy_inject_font' );
function clienvy_inject_font(): void {
	$font_url = esc_url( CLIENVY_PLUGIN_URL . 'assets/fonts/circular-std-400.woff' );
	echo "<style>@font-face { font-family: 'Circular Std'; font-weight: 400; src: url('{$font_url}') format('woff'); }</style>\n";
}

// ── Auth check modal ──────────────────────────────────────────────────────────
add_action( 'wp_enqueue_scripts', 'clienvy_enqueue_auth_check' );
add_action( 'admin_enqueue_scripts', 'clienvy_enqueue_auth_check' );
function clienvy_enqueue_auth_check(): void {
	$settings = get_option( 'clienvy_settings', [] );
	if ( empty( $settings['custom_login_styling_enabled'] ) ) {
		return;
	}
	wp_enqueue_style( 'clienvy-auth-check', CLIENVY_PLUGIN_URL . 'assets/css/auth-check.css', [], CLIENVY_VERSION );
}

// ── Bootstrap ─────────────────────────────────────────────────────────────────
new Clienvy_Updater( __FILE__, CLIENVY_GITHUB_REPO );
new Clienvy_Router();
new Clienvy_Settings();
new Clienvy_Login();

// ── Lifecycle hooks ───────────────────────────────────────────────────────────
register_activation_hook( __FILE__, 'clienvy_activate' );
function clienvy_activate(): void {
	if ( ! get_option( 'clienvy_connection_secret' ) ) {
		update_option( 'clienvy_connection_secret', clienvy_generate_secret() );
	}
	flush_rewrite_rules();
}

register_deactivation_hook( __FILE__, 'clienvy_deactivate' );
function clienvy_deactivate(): void {
	flush_rewrite_rules();
}

// ── Helpers ───────────────────────────────────────────────────────────────────

/**
 * Generates a cryptographically secure 48-character alphanumeric secret.
 */
function clienvy_generate_secret( int $length = 48 ): string {
	$chars  = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
	$secret = '';
	for ( $i = 0; $i < $length; $i++ ) {
		$secret .= $chars[ random_int( 0, strlen( $chars ) - 1 ) ];
	}
	return $secret;
}

