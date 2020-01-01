<?php

use WebTheory\Post2Post\SomewhatRelatableToPostTypeArg;
use WebTheory\Post2Post\TermRelatedPostsManager;
use WebTheory\SortOrder\SortByTermPostTypeArg;
use WebTheory\SortOrder\SortableTaxonomyArg;
use WebTheory\TaxRoles\StructuralTaxonomyArg;
use WebTheory\Taxtrubute\TermBasedPostMeta;

return [
    'data_managers' => [
        'term_based_post_meta' => TermBasedPostMeta::class,
        'term_related_posts' => TermRelatedPostsManager::class,
    ],

    'option_handlers' => [
        'post_type' => [
            'sort_by_term' => SortByTermPostTypeArg::class,
            'somewhat_relatable_to' => SomewhatRelatableToPostTypeArg::class,
        ],

        'taxonomy' => [
            'sortable' => SortableTaxonomyArg::class,
            'structural' => StructuralTaxonomyArg::class,
        ],
    ]
];
