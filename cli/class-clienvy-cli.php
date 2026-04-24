<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	return;
}

/**
 * Manages the Clienvy Connect plugin via WP-CLI.
 *
 * ## EXAMPLES
 *
 *     # Get the connection secret
 *     wp clienvy-connect secret
 *
 *     # Reset the connection secret
 *     wp clienvy-connect secret reset
 */
class Clienvy_CLI {

	/**
	 * Gets or resets the connection secret.
	 *
	 * ## OPTIONS
	 *
	 * [<action>]
	 * : Optional action. Use "reset" to regenerate the secret.
	 *
	 * ## EXAMPLES
	 *
	 *     wp clienvy-connect secret
	 *     wp clienvy-connect secret reset
	 *
	 * @subcommand secret
	 */
	public function secret( array $args, array $assoc_args ): void {
		if ( ( $args[0] ?? '' ) === 'reset' ) {
			$secret = Clienvy_Secret::generate();
			update_option( 'clienvy_connection_secret', $secret );
			WP_CLI::success( $secret );
			return;
		}

		$secret = get_option( 'clienvy_connection_secret' );

		if ( ! $secret ) {
			WP_CLI::error( 'No connection secret found. Is the plugin activated?' );
		}

		update_option( 'clienvy_secret_revealed', true );

        WP_CLI::success( $secret );
	}
}

WP_CLI::add_command( 'clienvy-connect', 'Clienvy_CLI' );
