<?php
/**
 * Plugin Name: Clienvy Connect
 * Plugin URI: https://clienvy.io
 * Description: Connect WordPress with Clienvy
 * Version: 1.0.9
 * Author: Clienvy
 * License: GPL-2.0+
 * Requires PHP: 7.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'CLIENVY_VERSION', '1.0.9' );
define( 'CLIENVY_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'CLIENVY_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'CLIENVY_GITHUB_REPO', 'clienvy/clienvy-wordpress-connect' );

// ── Includes (foundation) ─────────────────────────────────────────────────────
require_once CLIENVY_PLUGIN_DIR . 'includes/class-clienvy-i18n.php';
require_once CLIENVY_PLUGIN_DIR . 'includes/class-clienvy-secret.php';
require_once CLIENVY_PLUGIN_DIR . 'includes/class-clienvy-lifecycle.php';
require_once CLIENVY_PLUGIN_DIR . 'includes/class-clienvy-smtp.php';

// ── API ───────────────────────────────────────────────────────────────────────
require_once CLIENVY_PLUGIN_DIR . 'api/middleware/class-clienvy-auth.php';
require_once CLIENVY_PLUGIN_DIR . 'api/services/class-clienvy-settings-synchronize.php';
require_once CLIENVY_PLUGIN_DIR . 'api/services/class-clienvy-site-info.php';
require_once CLIENVY_PLUGIN_DIR . 'api/endpoints/class-endpoint-connect.php';
require_once CLIENVY_PLUGIN_DIR . 'api/endpoints/class-endpoint-synchronize.php';
require_once CLIENVY_PLUGIN_DIR . 'api/endpoints/class-endpoint-token.php';
require_once CLIENVY_PLUGIN_DIR . 'api/endpoints/class-endpoint-login.php';
require_once CLIENVY_PLUGIN_DIR . 'api/endpoints/class-endpoint-disconnect.php';
require_once CLIENVY_PLUGIN_DIR . 'api/class-clienvy-router.php';

// ── Admin ─────────────────────────────────────────────────────────────────────
require_once CLIENVY_PLUGIN_DIR . 'admin/api/middleware/class-clienvy-admin-auth.php';
require_once CLIENVY_PLUGIN_DIR . 'admin/api/endpoints/class-clienvy-settings-secret.php';
require_once CLIENVY_PLUGIN_DIR . 'admin/api/endpoints/class-clienvy-settings-smtp-test.php';
require_once CLIENVY_PLUGIN_DIR . 'admin/class-clienvy-settings-page.php';

// ── CLI ───────────────────────────────────────────────────────────────────────
require_once CLIENVY_PLUGIN_DIR . 'cli/class-clienvy-cli.php';

// ── Updater ───────────────────────────────────────────────────────────────────
require_once CLIENVY_PLUGIN_DIR . 'updater/class-clienvy-updater.php';

// ── Frontend ──────────────────────────────────────────────────────────────────
require_once CLIENVY_PLUGIN_DIR . 'frontend/class-clienvy-login-page.php';

// ── Lifecycle ─────────────────────────────────────────────────────────────────
Clienvy_Lifecycle::register( __FILE__ );

// ── Bootstrap ─────────────────────────────────────────────────────────────────
new Clienvy_Updater( __FILE__, CLIENVY_GITHUB_REPO );
new Clienvy_Router();
new Clienvy_Settings();
new Clienvy_Login();
new Clienvy_SMTP();
