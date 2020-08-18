<?php

use WebTheory\Leonidas\Auth\Nonce;
use WebTheory\Leonidas\Forms\Controllers\PostMetaBoxFormSubmissionManager;
use WebTheory\Leonidas\MetaBox\Field;
use WebTheory\Leonidas\MetaBox\MetaBox;
use WebTheory\Leonidas\Screen;
use WebTheory\Post2Post\PostRelationship;
use WebTheory\Post2Post\PostRelationshipChecklist;
use WebTheory\Post2Post\SomewhatRelatablePostType;

################################################################################
# Initiate Relationships
################################################################################
add_action('init', function () {
    new SomewhatRelatablePostType(get_post_type_object('ba_location'), 'ba_menu_item');
    new SomewhatRelatablePostType(get_post_type_object('ba_menu_item'), 'ba_location');
});

################################################################################
# Location checklist
################################################################################
Screen::load('post', ['post_type' => 'ba_location'], function () {

    $postType = 'ba_location';
    $nonce = new Nonce('post-2-post-nonce', 'save-thing');
    $relationship = new PostRelationship($postType, 'ba_menu_item');

    $controller = new PostRelationshipChecklist('test-thing', $relationship, $postType);
    $field = (new Field($controller))->setLabel('Menu Items');

    (new MetaBox('test', 'Post2Post Test', $postType))
        ->setNonce($nonce)
        ->addContent('menu_items', $field)
        ->hook();

    (new PostMetaBoxFormSubmissionManager($postType))
        ->setNonce($nonce)
        ->addField($controller)
        ->hook();
});


################################################################################
# Menu Item checklist
################################################################################
Screen::load('post', ['post_type' => 'ba_menu_item'], function () {

    $postType = 'ba_menu_item';
    $nonce = new Nonce('post-2-post-nonce', 'save-other-thing');
    $relationship = new PostRelationship($postType, 'ba_location');

    $controller = new PostRelationshipChecklist('test-thing', $relationship, $postType);
    $field = (new Field($controller))->setLabel('Locations Available');

    (new MetaBox('test', 'Post2Post Test', $postType))
        ->setNonce($nonce)
        ->addContent('locations', $field)
        ->hook();

    (new PostMetaBoxFormSubmissionManager($postType))
        ->setNonce($nonce)
        ->addField($controller)
        ->hook();
});
