<?php

/**
 * @package Backalley-Starter
 */

namespace Backalley;

abstract class SortableObjectsBase
{
    /**
     * 
     */
    public static function order_objects_array($objects, $object_type, $orderby_apex, $orderby_hierarchy)
    {
        $properties = GuctilityBelt::infer_object_properties($object_type);

        $object_id = $properties['object_id'];
        $object_parent = $properties['object_parent'];

        $apex_objects = [];
        $apex_order = [];

        $hierarchy_objects = [];
        $hierarchy_order = [];

        foreach ($objects as $object) {
            if (empty($object->$object_parent)) {
                $apex_objects[] = $object;
                $apex_order[$object->$object_id] = (int)get_metadata($object_type, $object->$object_id, $orderby_apex, true);
            }

            if (!empty($object->$object_parent)) {
                $hierarchy_objects[] = $object;
                $hierarchy_order[$object->$object_id] = (int)get_metadata($object_type, $object->$object_id, $orderby_hierarchy, true);
            }
        }

        $apex_objects = GuctilityBelt::sort_objects_array($apex_objects, $apex_order, $object_id);
        $hierarchy_objects = GuctilityBelt::sort_objects_array($hierarchy_objects, $hierarchy_order, $object_id);

        // $apex_objects = GuctilityBelt::sort_objects_by_meta($apex_objects, $object_type, $orderby_apex);
        // $hierarchy_objects = GuctilityBelt::sort_objects_by_meta($hierarchy_objects, $object_type, $orderby_hierarchy);

        return array_merge($apex_objects, $hierarchy_objects);
    }
}
