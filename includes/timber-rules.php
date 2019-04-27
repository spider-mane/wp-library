<?php

use function DeepCopy\deep_copy;

add_filter('timber/twig', function ($twig) {
    // die(var_dump($twig));
    $twig->addExtension(new Twig_Extension_StringLoader());
    $twig->addFilter(new Twig_SimpleFilter('subjectify_objects', 'backalley_subjectify_wp_objects'));
    
    $twig->addFilter(new Twig_SimpleFilter('clone_original', function ($original) {
        return deep_copy($original);
    }));

    $twig->addFilter(new Twig_SimpleFilter('sort_terms_hierarchicaly', function ($var) {
        // $filtered = [];
        sort_terms_hierarchicaly($var, $filtered);
        return $filtered;
    }));

    $twig->addFunction(new Twig_Function('wp_submit_button', 'submit_button'));

    return $twig;
});
