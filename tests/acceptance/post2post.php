<?php

use WebTheory\Leonidas\Fields\WpAdminField;
use WebTheory\Leonidas\Forms\Controllers\PostMetaBoxFormSubmissionManager;
use WebTheory\Leonidas\MetaBox\Field;
use WebTheory\Leonidas\MetaBox\MetaBox;
use WebTheory\Leonidas\Screen;
use WebTheory\Post2Post\TermRelatedPostsManager;
use WebTheory\Saveyour\Fields\Checklist;

########################################################################################################################

################################################################################
# 
################################################################################
Screen::load('post', ['post_type' => 'ba_location'], function () {

    /**
     *
     */
    $postType = 'ba_location';
    $formController = new PostMetaBoxFormSubmissionManager($postType);

    // create metabox
    $metabox = (new MetaBox('test', 'Test'))
        ->setScreen($postType)
        ->setContext('normal')
        ->hook();


    $nonce = $metabox->getNonce();
    $formController
        ->setNonce($nonce['name'], $nonce['action'])
        // ->addGroup('address', $addressGeoGroup)
        ->hook();

    $posts = get_posts([
        'post_type' => 'ba_menu_item',
        'posts_per_page' => -1,
        'orderby' => 'name',
        'order' => 'ASC',
    ]);

    $items = [];
    foreach ($posts as $post) {
        $items[$post->post_name] = [
            'value' => '1',
            'label' => $post->post_title,
            'name' => (string) $post->ID,
            'id' => "ba--location-menu-item--{$post->post_name}",
        ];
    }

    $manager = new TermRelatedPostsManager('_ba_location_', 'ba_menu_item', $postType);
    $field = (new Checklist)
        ->setId('ba-location--menu_items')
        ->setItems($items)
        ->setToggleControl('0')
        ->addClass('thing');


    $controller = (new WpAdminField('menu_items', $field, $manager));
    $checklist = (new Field($controller))->setLabel('Menu Items');

    $metabox->addContent('menu_items', $checklist);
    $formController->addField($controller);
});


################################################################################
# 
################################################################################
Screen::load('post', ['post_type' => 'ba_menu_item'], function () {

    $postType = 'ba_menu_item';

    $metabox = (new MetaBox('test', 'Test'))
        ->setScreen($postType)
        ->setContext('normal');

    $nonce = $metabox->getNonce();
    $formManager = (new PostMetaBoxFormSubmissionManager($postType))
        ->setNonce($nonce['name'], $nonce['action']);


    $posts = get_posts([
        'post_type' => 'ba_location',
        'posts_per_page' => -1,
        'orderby' => 'name',
        'order' => 'ASC',
    ]);

    $items = [];
    foreach ($posts as $post) {
        $items[(string) $post->ID] = [
            'value' => (string) $post->ID,
            'label' => $post->post_title,
            'id' => "ba--location-menu-item--{$post->post_name}",
        ];
    }

    $manager = new TermRelatedPostsManager('_ba_location_', $postType, 'ba_location');
    $field = (new Checklist)
        ->setId('ba-location--menu_items')
        ->addClass('thing')
        ->setItems($items);
    $controller = (new WpAdminField('ba_menu_item__locations', $field, $manager));
    $checklist = (new Field($controller))->setLabel('Locations Available');

    $metabox->addContent('locations', $checklist)->hook();

    $formManager->addField($controller)->hook();
});
