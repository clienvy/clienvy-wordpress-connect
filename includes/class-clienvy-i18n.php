<?php

if( !defined('ABSPATH') ) {
    exit;
}


class Clienvy_I18n
{

    private const DEFAULT_LOCALE = 'nl-NL';

    private static ?array $messages = null;

    private static string $locale = self::DEFAULT_LOCALE;


    public static function t(string $key, array $replacements = []): string
    {
        if( self::$messages === null ) {
            self::load();
        }

        $value = self::lookup($key);
        if( !is_string($value) ) {
            return $key;
        }

        foreach ( $replacements as $token => $replacement ) {
            $value = str_replace('{' . $token . '}', (string)$replacement, $value);
        }

        return $value;
    }


    public static function set_locale(string $locale): void
    {
        if( $locale === self::$locale ) {
            return;
        }
        self::$locale = $locale;
        self::$messages = null;
    }


    public static function locale(): string
    {
        return self::$locale;
    }


    private static function load(): void
    {
        $path = CLIENVY_PLUGIN_DIR . 'assets/language/' . self::$locale . '.json';

        if( !file_exists($path) ) {
            self::$messages = [];
            return;
        }

        $contents = file_get_contents($path);
        $decoded = json_decode($contents, true);

        self::$messages = is_array($decoded) ? $decoded : [];
    }


    private static function lookup(string $key)
    {
        $current = self::$messages;

        foreach ( explode('.', $key) as $part ) {
            if( !is_array($current) || !array_key_exists($part, $current) ) {
                return null;
            }
            $current = $current[ $part ];
        }

        return $current;
    }
}
