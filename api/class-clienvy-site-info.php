<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Collects the site data that the Clienvy synchronises on every connect/synchronize call.
 */
class Clienvy_Site_Info {

	public static function collect(): array {
		return [
			'site_name' => get_bloginfo( 'name' ),
			'administrator_email' => get_bloginfo( 'admin_email' ),
			'wordpress_version' => get_bloginfo( 'version' ),
			'php_version' => PHP_VERSION,
			'plugin_version' => CLIENVY_VERSION,
			'no_index' => (bool) get_option( 'blog_public' ) === false,
		];
	}
}
