<?php

use Backalley\Form\Controllers\FormFieldController;
use Backalley\Form\Fields\Checklist;
use Backalley\Library;
use Backalley\Post2Post\TermRelatedPostsManager;
use Backalley\WordPress\Forms\Controllers\PostMetaBoxFormSubmissionManager;
use Backalley\WordPress\MetaBox\MetaBox;
use Backalley\WordPress\PostType\Factory as PostTypeFactory;
use Backalley\WordPress\Screen;
use Backalley\WordPress\Taxonomy\Factory as TaxonomyFactory;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;


Library::init();

(new Run)->prependHandler(new PrettyPageHandler)->register(); // error handling with whoops


add_action('init', function () {

    $app = require 'config/app.php';
    $postTypeHandlers = $app['option_handlers']['post_type'];
    $taxonomyHandlers = $app['option_handlers']['taxonomy'];

    $postTypes = require 'config/post_types.php';
    $taxonomies = require 'config/taxonomies.php';

    $postTypes = (new PostTypeFactory($postTypeHandlers))->create($postTypes);
    $taxonomies = (new TaxonomyFactory($taxonomyHandlers))->create($taxonomies);

    require 'admin-page.php';
});

/**
 *
 */
Screen::load('post', ['post_type' => 'ba_location'], function () {
    include 'fields.php';
});

/**
 *
 */
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
    $controller = (new FormFieldController('ba_menu_item__locations', $field, $manager));
    $checklist = (new Field('thing2', $controller))->setLabel('Locations Available');

    $metabox->addContent('locations', $checklist)->hook();

    $formManager->addField($controller)->hook();
});
