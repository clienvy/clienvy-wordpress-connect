<?php

if( !defined('ABSPATH') ) {
    exit;
}


class Clienvy_SMTP
{

    public function __construct()
    {
        add_action('phpmailer_init', [$this, 'configure']);
        add_filter('wp_mail_from', [$this, 'filter_from_email']);
        add_filter('wp_mail_from_name', [$this, 'filter_from_name']);
    }


    public function filter_from_email(string $email): string
    {
        $settings = get_option('clienvy_settings', []);

        if( empty($settings[ 'smtp_enabled' ]) ) {
            return $email;
        }

        $sender = trim((string)($settings[ 'smtp_sender_email' ] ?? ''));
        return ($sender !== '' && is_email($sender)) ? $sender : $email;
    }


    public function filter_from_name(string $name): string
    {
        $settings = get_option('clienvy_settings', []);

        if( empty($settings[ 'smtp_enabled' ]) ) {
            return $name;
        }

        $sender = trim((string)($settings[ 'smtp_sender_name' ] ?? ''));
        return $sender !== '' ? $sender : $name;
    }


    public function configure($phpmailer): void
    {
        $settings = get_option('clienvy_settings', []);

        if( empty($settings[ 'smtp_enabled' ]) ) {
            return;
        }

        $host = $settings[ 'smtp_host' ] ?? '';
        $port = (int)($settings[ 'smtp_port' ] ?? 0);

        if( $host === '' || $port <= 0 ) {
            return;
        }

        $username = $settings[ 'smtp_username' ] ?? '';
        $password = $settings[ 'smtp_password' ] ?? '';

        $phpmailer->isSMTP();
        $phpmailer->Host = $host;
        $phpmailer->Port = $port;
        $phpmailer->SMTPSecure = ($port === 465) ? 'ssl' : 'tls';

        if( $username !== '' ) {
            $phpmailer->SMTPAuth = true;
            $phpmailer->Username = $username;
            $phpmailer->Password = $password;
        }
        else {
            $phpmailer->SMTPAuth = false;
        }

        if( !empty($settings[ 'smtp_reply_to_enabled' ]) ) {
            $reply_to_email = trim((string)($settings[ 'smtp_reply_to_email' ] ?? ''));
            if( $reply_to_email !== '' && is_email($reply_to_email) ) {
                $reply_to_name = trim((string)($settings[ 'smtp_reply_to_name' ] ?? ''));
                $phpmailer->clearReplyTos();
                $phpmailer->addReplyTo($reply_to_email, $reply_to_name);
            }
        }
    }
}
