<?php

/**
 * @package Backalley-Starter
 */

namespace WebTheory\SortOrder;

use function WebTheory\GuctilityBelt\sort_objects_array;
use function WebTheory\Leonidas\Helpers\infer_object_properties;

abstract class SortableObjectsBase
{
    /**
     *
     */
    public static function order_objects_array($objects, $object_type, $orderby_apex, $orderby_hierarchy)
    {
        $properties = infer_object_properties($object_type);

        $object_id = $properties['object_id'];
        $object_parent = $properties['object_parent'];

        $apex_objects = [];
        $apex_order = [];

        $hierarchy_objects = [];
        $hierarchy_order = [];

        foreach ($objects as $object) {
            if (empty($object->$object_parent)) {
                $apex_objects[] = $object;
                $apex_order[$object->$object_id] = (int) get_metadata($object_type, $object->$object_id, $orderby_apex, true);
            }

            if (!empty($object->$object_parent)) {
                $hierarchy_objects[] = $object;
                $hierarchy_order[$object->$object_id] = (int) get_metadata($object_type, $object->$object_id, $orderby_hierarchy, true);
            }
        }

        $apex_objects = sort_objects_array($apex_objects, $apex_order, $object_id);
        $hierarchy_objects = sort_objects_array($hierarchy_objects, $hierarchy_order, $object_id);

        return array_merge($apex_objects, $hierarchy_objects);
    }
}
