<?php

add_action('admin_enqueue_scripts', function () {
    # wp included libraries
    wp_enqueue_script('jquery');
    wp_enqueue_script('jquery-ui-sortable');

    # backalley scripts
    wp_enqueue_script('backalley-starter-script--sort-objects', BackAlley_Library::$url . 'assets/js/backalley-sortable-objects.js', null, time(), true);
    
    # backalley styles
    wp_enqueue_style('backalley-starter-styles--sort-objects', BackAlley_Library::$url . 'assets/css/backalley-sortable-objects.css', null, time());
});
