<?php

use WebTheory\Leonidas\PostType\Factory as PostTypeFactory;
use WebTheory\Leonidas\Taxonomy\Factory as TaxonomyFactory;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;

################################################################################
# error handling
################################################################################

// add_action('init', function () {
//     (new Run)->prependHandler(new PrettyPageHandler)->register(); // error handling with whoops
// });

// wp_delete_term(2, '&shadow->ba_location');
// wp_delete_term(3, '&shadow->ba_menu_item');

################################################################################
# register models
################################################################################
add_action('init', function () {

    $app = require 'config/app.php';
    $postTypeHandlers = $app['option_handlers']['post_type'];
    $taxonomyHandlers = $app['option_handlers']['taxonomy'];

    $postTypes = require 'config/post_types.php';
    $taxonomies = require 'config/taxonomies.php';

    $postTypes = (new PostTypeFactory($postTypeHandlers))->create($postTypes);
    $taxonomies = (new TaxonomyFactory($taxonomyHandlers))->create($taxonomies);
});

################################################################################
# load test files
################################################################################
require 'post2post.php';
require 'taxtribute.php';
