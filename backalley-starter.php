<?php

/**
 * This file is part of the Backalley package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package   WpLibrary
 * @license   GNU GPL
 * @copyright Copyright (C) WebTheory Studio, All rights reserved.
 * @link      https://github.com/spider-mane/backalley
 * @author    Chris Williams <christwilhelm84@gmail.com>
 */

if (!class_exists('BackalleyLibraryBase')) {

    /**
     *
     */
    class BackalleyLibraryBase
    {
        public static $url;
        public static $path;
        public static $base;
        public static $admin_url;
        public static $admin_templates;
        public static $timber_locations;

        public static function load()
        {
            Self::$path = __DIR__;
            Self::$url = plugin_dir_url(__FILE__);
            Self::$base = plugin_basename(__FILE__);

            Self::$admin_url = Self::$url . "public/admin";
            Self::$admin_templates = Self::$path . "/public/admin/templates";

            Self::$timber_locations = [
                Self::$admin_templates,
                Self::$admin_templates . '/macros',
            ];
        }
    }
}

#Composer Autoload
if (file_exists(dirname(__FILE__) . '/vendor/autoload.php')) {
    require_once dirname(__FILE__) . '/vendor/autoload.php';
}
