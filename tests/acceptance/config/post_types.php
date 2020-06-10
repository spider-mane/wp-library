<?php

return [

    /**
     *
     */
    "ba_menu_item" => [

        "can_export" => true,
        "capability_type" => "post",
        "exclude_from_search" => false,
        "has_archive" => true,
        "hierarchical" => false,
        "menu_icon" => "dashicons-carrot",
        "menu_position" => 5,
        "public" => true,
        "publicly_queryable" => true,
        "show_in_admin_bar" => true,
        "show_in_menu" => true,
        "show_in_nav_menus" => true,
        "show_in_rest" => true,
        "show_ui" => true,
        "supports" => ["title", "_price"],

        "description" => "Product information pages.",

        "labels" => [
            "name" => "Menu Items",
            "singular_name" => "Menu Item",
            "menu_name" => "Menu"
        ],

        "rewrite" => [
            "slug" => "menu",
            "with_front" => true,
            "pages" => false,
            "feeds" => true
        ],

        "options" => [
            "sort_by_term" => [
                "taxonomy" => "ba_menu_category",

                "submenu_page" => [
                    "capability" => "manage_options",
                    "submenu_file" => "ba_sort_terms&taxonomy=ba_menu_category",
                    "display" => false
                ],

                "term_row_action" => true
            ]
        ]
    ],

    /**
     *
     */
    "ba_location" => [

        "can_export" => true,
        "capability_type" => "post",
        "exclude_from_search" => false,
        "has_archive" => true,
        "hierarchical" => false,
        "menu_icon" => "dashicons-location-alt",
        "menu_position" => 5,
        "public" => true,
        "publicly_queryable" => true,
        "show_in_admin_bar" => true,
        "show_in_menu" => true,
        "show_in_nav_menus" => true,
        "show_in_rest" => true,
        "show_ui" => true,
        "supports" => ["title", "thumbnail"],

        "labels" => [
            "name" => "Locations",
            "singular_name" => "Location"
        ],

        "rewrite" => [
            "slug" => "locations",
            "with_front" => true,
            "pages" => true,
            "feeds" => true
        ],

        "options" => [
            "relationships" => [[
                'name' => 'wts-location->menu',
                'relatable_to' => 'ba_menu_item'
            ]]
        ]
    ],

    /**
     *
     */
    "ba_press_review" => [

        "can_export" => true,
        "capability_type" => "post",
        "exclude_from_search" => false,
        "has_archive" => true,
        "hierarchical" => false,
        "menu_icon" => "dashicons-heart",
        "menu_position" => 5,
        "public" => true,
        "publicly_queryable" => true,
        "show_in_admin_bar" => true,
        "show_in_menu" => true,
        "show_in_nav_menus" => true,
        "show_in_rest" => true,
        "show_ui" => true,
        "supports" => ["title"],

        "labels" => [
            "name" => "Press Reviews",
            "singular_name" => "Press Review"
        ],

        "rewrite" => [
            "slug" => "press",
            "with_front" => true,
            "pages" => true,
            "feeds" => true
        ]
    ],

    /**
     *
     */
    "ba_celeb_guest" => [

        "can_export" => true,
        "capability_type" => "post",
        "exclude_from_search" => false,
        "has_archive" => true,
        "hierarchical" => false,
        "menu_icon" => "dashicons-star-filled",
        "menu_position" => 5,
        "public" => true,
        "publicly_queryable" => true,
        "show_in_admin_bar" => true,
        "show_in_menu" => true,
        "show_in_nav_menus" => true,
        "show_in_rest" => true,
        "show_ui" => true,
        "supports" => ["title", "thumbnail"],

        "description" => "Celebrity and other notable guests",

        "labels" => [
            "name" => "Celebrity Guests",
            "singular_name" => "Celebrity Guest"
        ],

        "rewrite" => [
            "slug" => "celebrity-guests",
            "with_front" => true,
            "pages" => true,
            "feeds" => true
        ]
    ]
];
