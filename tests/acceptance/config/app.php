<?php

use WebTheory\Post2Post\Prefixer;
use WebTheory\Post2Post\SomewhatRelatableToPostTypeArg;
use WebTheory\Post2Post\TermRelatedPostsManager;
use WebTheory\SortOrder\SortByTermPostTypeArg;
use WebTheory\SortOrder\SortableTaxonomyArg;
use WebTheory\TaxRoles\StructuralTaxonomyArg;
use WebTheory\Taxtrubute\TermBasedPostMeta;

$prefix = new Prefixer('ba');
$_ = [$prefix, 'underscore'];

return [
    'data_managers' => [
        'term_based_post_meta' => TermBasedPostMeta::class,
        'term_related_posts' => TermRelatedPostsManager::class,
    ],

    'option_handlers' => [
        'post_type' => [
            'sort_by_term' => SortByTermPostTypeArg::class,
            // 'relationships' => SomewhatRelatableToPostTypeArg::class,
        ],

        'taxonomy' => [
            'sortable' => SortableTaxonomyArg::class,
            'structural' => StructuralTaxonomyArg::class,
        ],
    ],

    'relationships' => [
        [$_('location'), $_('menu_item')],
        [$_('location'), $_('press_review')],
    ]
];
