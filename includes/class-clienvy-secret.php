<?php

if( !defined('ABSPATH') ) {
    exit;
}


class Clienvy_Secret
{

    public static function generate(int $length = 48): string
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $secret = '';
        for ( $i = 0; $i < $length; $i++ ) {
            $secret .= $chars[ random_int(0, strlen($chars) - 1) ];
        }
        return $secret;
    }
}
