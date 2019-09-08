<?php

/**
 * @package Backalley-Starter
 */

namespace Backalley\SortableObjects;

class SortableObjectsWalker extends \Walker
{
    public $db_fields = ['parent' => '', 'id' => '', 'title' => ''];

    public $tree_type = '';

    /**
     *
     */
    public function set_object_type(string $object_type)
    {
        $this->tree_type = $object_type;

        switch ($this->tree_type) {
            case 'post':
                $this->db_fields['parent'] = 'post_parent';
                $this->db_fields['id'] = 'ID';
                $this->db_fields['title'] = 'post_title';
                break;

            case 'term':
                $this->db_fields['parent'] = 'parent';
                $this->db_fields['id'] = 'term_id';
                $this->db_fields['title'] = 'name';
                break;
        }
    }

    /**
     *
     */
    public function start_lvl(&$output, $depth = 0, $args = [])
    {
        $indent = str_repeat("\t", $depth);
        $ul_classes = $args['ul_classes'] ?? '';
        // $ul_classes = 'hierarchy sortable sortable--group';

        $output .= "\n{$indent}<ul id='' class='${ul_classes}'>\n";
    }

    /**
     *
     */
    public function end_lvl(&$output, $depth = 0, $args = [])
    {
        $indent = str_repeat("\t", $depth);

        $output .= "{$indent}</ul>\n";
    }

    /**
     *
     */
    public function start_el(&$output, $object, $depth = 0, $args = [], $current_object_id = 0)
    {
        // var_dump($object);
        $indent = !empty($depth) ? str_repeat("\t", $depth) : '';


        $parent_field = $this->db_fields['parent'];
        $id_field = $this->db_fields['id'];
        $title_field = $this->db_fields['title'];

        $object_id = $object->{$id_field};

        $apex_display_position = (int) get_metadata($this->tree_type, $object_id, $args['apex_meta_key'], true);
        $hierarchy_display_position = (int) get_metadata($this->tree_type, $object_id, $args['hierarchy_meta_key'], true);

        // input values
        // $common_input_classes = 'order-input small 0hide-if-js';
        $common_input_classes = $args['common_input_classes'];
        $min = 0;

        $apex_input_id = "";
        $apex_input_name = "ba_order[{$object_id}]";
        // $apex_input_classes = $common_input_classes . ' order-input--apex';
        $apex_input_classes = $common_input_classes . ' ' . $args['apex_input_classes'];
        $apex_max = 100;

        $hierarchy_input_id = "";
        $hierarchy_input_name = "ba_hierarchy_order[{$object_id}]";
        // $hierarchy_input_classes = $common_input_classes . ' order-input--hierarchy';
        $hierarchy_input_classes = $common_input_classes . ' ' . $args['hierarchy_input_classes'];
        $hierarchy_max = 100;
        // end input values

        $li_classes = $args['li_classes'] ?? '';
        $div_classes = 'sortable--item';
        // $li_classes = 'sortable--item-container';

        $after_title = $args['after_title'] ?? null;
        $before_end = $args['before_end'] ?? null;


        // render output
        $output .= "<li class='{$li_classes}' id='ba--object-{$object_id}'>\n";
        $output .= "<div class='{$div_classes}'>\n";
        $output .= "<h3 class='object-title'>{$object->{$title_field}}</h3>\n";

        if (!empty($after_title)) {
            $output .= "<span>{$after_title}<span>\n";
        }

        if (!empty($before_end)) {
            $output .= "<span>{$before_end}<span>\n";
        }

        $output .= "<span class='input-container 0hide-if-js'>\n";

        if (empty($object->{$parent_field})) {
            $output .= "<input type='number' name='{$apex_input_name}' id='' class='{$apex_input_classes}' value={$apex_display_position} min={$min} max={$apex_max}>\n";
        }

        if (!empty($object->{$parent_field})) {
            $output .= "<input type='number' name='{$hierarchy_input_name}' id='' class='{$hierarchy_input_classes}' value={$hierarchy_display_position} min={$min} max={$hierarchy_max}>\n";
        }

        $output .= "</span>\n";
        $output .= "</div>\n";
    }

    /**
     *
     */
    public function end_el(&$output, $object, $depth = 0, $args = [])
    {
        $output .= "</li>\n";
    }
}
