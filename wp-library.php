<?php

/**
 * This file is part of the WebTheory WpLibrary package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package   WpLibrary
 * @license   GPL-3.0-or-later
 * @copyright Copyright (C) Chris Williams, All rights reserved.
 * @link      https://github.com/spider-mane/wp-library
 * @author    Chris Williams <spider.mane.web@gmail.com>
 */

if (!class_exists('WebTheoryWpLibrary')) {

    abstract class WebTheoryWpLibrary
    {
        protected static $url;
        protected static $path;
        protected static $base;
        protected static $admin_url;
        protected static $admin_templates;

        protected static function load()
        {
            static::$path = __DIR__;
            static::$url = plugin_dir_url(__FILE__);
            static::$base = plugin_basename(__FILE__);

            static::$admin_url = static::$url . "public/admin";
            static::$admin_templates = static::$path . "/public/admin/templates";
        }

        public static function get(string $property)
        {
            return Self::${$property};
        }
    }
}

#Composer Autoload
if (file_exists($autoload = dirname(__FILE__) . '/vendor/autoload.php')) {
    require_once $autoload;
}
