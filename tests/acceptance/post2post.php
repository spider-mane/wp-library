<?php

use WebTheory\Leonidas\Auth\Nonce;
use WebTheory\Leonidas\Forms\Controllers\PostMetaBoxFormSubmissionManager;
use WebTheory\Leonidas\MetaBox\Field;
use WebTheory\Leonidas\MetaBox\MetaBox;
use WebTheory\Leonidas\Screen;
use WebTheory\Post2Post\FormField;
use WebTheory\Post2Post\Repository;

################################################################################
# Location checklist
################################################################################
Screen::load('post', ['post_type' => 'ba_location'], function () {

    $postType = 'ba_location';
    $nonce = new Nonce('post-2-post-nonce', 'save-thing');
    $relationship = Repository::getRelationship('wts-location->menu');

    $controller = new FormField('test-thing', 'relatable', $relationship);
    $field = (new Field($controller))->setLabel('Menu Items');

    (new MetaBox('test', 'Test', $postType))
        ->addContent('menu_items', $field)
        ->setNonce($nonce)
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
    $relationship = Repository::getRelationship('wts-location->menu');

    $controller = new FormField('test-thing', 'related', $relationship);
    $field = (new Field($controller))->setLabel('Locations Available');

    (new MetaBox('test', 'Test', $postType))
        ->setNonce($nonce)
        ->addContent('locations', $field)
        ->hook();

    (new PostMetaBoxFormSubmissionManager($postType))
        ->setNonce($nonce)
        ->addField($controller)
        ->hook();
});
