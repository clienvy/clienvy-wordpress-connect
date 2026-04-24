<?php

if( !defined('ABSPATH') ) {
    exit;
}


class Clienvy_Lifecycle
{

    public static function register(string $plugin_file): void
    {
        register_activation_hook($plugin_file, [self::class, 'activate']);
        register_deactivation_hook($plugin_file, [self::class, 'deactivate']);
    }


    public static function activate(): void
    {
        if( !get_option('clienvy_connection_secret') ) {
            update_option('clienvy_connection_secret', Clienvy_Secret::generate());
        }
        flush_rewrite_rules();
    }


    public static function deactivate(): void
    {
        flush_rewrite_rules();
    }
}
