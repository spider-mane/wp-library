<?php

namespace Backalley\WP\PostType\Concepts;

/**
 * @package Backalley-Core
 */
abstract class ConceptualPostType
{
    /**
     * 
     */
    public static $meta_boxes = [];

    /**
     * 
     */
    public static $list_table_columns = [];

    /**
     *
     */
    abstract public function set_meta_boxes();

    /**
     * 
     */
    abstract public function set_list_table_columns();
}
