<?php namespace WpEnv;

class WpEnv
{
    private static $loaders = [ ];

    public static function register_loader( Loader $loader )
    {
        static::$loaders[ ] = $loader;
    }

    /**
     * Activate loaders
     */
    public static function init()
    {
        foreach ( static::$loaders as $loader )
        {
            $loader->enforce();
        }
    }
}