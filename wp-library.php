<?php

#Composer Autoload

use WebTheory\WpLibrary;

if (file_exists($autoload = __DIR__ . '/vendor/autoload.php')) {
    require_once $autoload;
}

# define filesystem variables in base class
if (!class_exists('WebTheoryWpLibrary')) {

    abstract class WebTheoryWpLibrary
    {
        protected static $url;
        protected static $path;
        protected static $base;
        protected static $assets;
        protected static $templates;

        protected static $loaded = false;

        protected static function load()
        {
            static::$path = __DIR__;
            static::$url = plugin_dir_url(__FILE__);
            static::$base = plugin_basename(__FILE__);

            static::$assets = static::$url . "assets/dist";
            static::$templates = static::$path . "/templates";

            static::$loaded = true;
        }

        public static function get(string $property)
        {
            return Self::${$property};
        }

        public static function isLoaded()
        {
            return static::$loaded;
        }
    }
}

# bootstrap plugin
if (!WpLibrary::isLoaded()) {
    WpLibrary::init();
}
