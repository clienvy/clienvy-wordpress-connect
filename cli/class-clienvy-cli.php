<?php

if( !defined('ABSPATH') ) {
    exit;
}

if( !defined('WP_CLI') || !WP_CLI ) {
    return;
}


class Clienvy_CLI
{

    public function secret(array $args, array $assoc_args): void
    {
        if( ($args[ 0 ] ?? '') === 'reset' ) {
            $secret = Clienvy_Secret::generate();
            update_option('clienvy_connection_secret', $secret);
            WP_CLI::success($secret);
            return;
        }

        $secret = get_option('clienvy_connection_secret');

        if( !$secret ) {
            WP_CLI::error('No connection secret found. Is the plugin activated?');
        }

        update_option('clienvy_secret_revealed', true);

        WP_CLI::success($secret);
    }
}


WP_CLI::add_command('clienvy-connect', 'Clienvy_CLI');
