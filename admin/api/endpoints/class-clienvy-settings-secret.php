<?php

if( !defined('ABSPATH') ) {
    exit;
}


/**
 * AJAX handlers for revealing and resetting the connection secret.
 */
class Clienvy_Settings_Secret
{

    public function __construct()
    {
        add_action('wp_ajax_clienvy_reveal_secret', [$this, 'ajax_reveal_secret']);
        add_action('wp_ajax_clienvy_reset_secret', [$this, 'ajax_reset_secret']);
    }


    public function ajax_reveal_secret(): void
    {
        Clienvy_Admin_Auth::check();

        if( get_option('clienvy_secret_revealed') ) {
            wp_send_json_error(Clienvy_I18n::t('_admin._endpoints._secret.already_revealed'), 403);
        }

        $secret = get_option('clienvy_connection_secret');
        if( !$secret ) {
            $secret = Clienvy_Secret::generate();
            update_option('clienvy_connection_secret', $secret);
        }

        update_option('clienvy_secret_revealed', true);
        wp_send_json_success(['secret' => $secret]);
    }


    public function ajax_reset_secret(): void
    {
        Clienvy_Admin_Auth::check();

        $secret = Clienvy_Secret::generate();
        update_option('clienvy_connection_secret', $secret);
        update_option('clienvy_secret_revealed', true);
        delete_option('clienvy_settings');
        flush_rewrite_rules();

        wp_send_json_success(['secret' => $secret]);
    }
}
