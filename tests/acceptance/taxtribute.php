<?php

use Respect\Validation\Validator as v;
use WebTheory\Leonidas\Auth\Nonce;
use WebTheory\Leonidas\Fields\TermChecklist;
use WebTheory\Leonidas\Forms\Controllers\PostMetaBoxFormSubmissionManager;
use WebTheory\Leonidas\MetaBox\Field;
use WebTheory\Leonidas\MetaBox\MetaBox;
use WebTheory\Leonidas\MetaBox\Section;
use WebTheory\Leonidas\Screen;
use WebTheory\Saveyour\Controllers\FormFieldControllerBuilder;
use WebTheory\Saveyour\Fields\Url;
use WebTheory\Taxtribute\Constrainer;
use WebTheory\Taxtribute\Model;
use WebTheory\Taxtribute\TermBasedPostMeta;

Screen::load('post', ['post_type' => 'ba_location'], function () {
    // exit(var_dump($_POST));

    ################################################################################
    # Base
    ################################################################################
    $postType = 'ba_location';
    $taxonomy = 'ba_delivery_platforms';
    $taxName = get_taxonomy($taxonomy)->labels->name;

    $taxtribute = new Model($taxonomy);
    $nonce = new Nonce('taxtribute-nonce', 'save-taxtributes');

    $metabox = (new MetaBox('taxtribute-metabox', 'Taxtribute Test', $postType))
        ->setNonce($nonce)
        ->hook();

    $manager = (new PostMetaBoxFormSubmissionManager($postType))
        ->setNonce($nonce)
        ->hook();

    $section = (new Section($taxName));
    $metabox->addContent('test', $section);

    ################################################################################
    # Attributes
    ################################################################################
    $checklist = new TermChecklist($taxonomy, 'wts_tax_input');
    $checklistField = (new Field($checklist))->setLabel('');

    $manager->addField($checklist);
    $section->addContent('taxtribute-checklist', $checklistField);

    ################################################################################
    # Values
    ################################################################################
    $attributes = $taxtribute->getAttributes();

    foreach ($attributes as $attribute) {
        $name = $attribute->name;
        $attribute = $attribute->slug;

        $field = (new Url)->addClass('large-text');
        $dataManager = new TermBasedPostMeta($attribute, $taxtribute);

        $controller = (new FormFieldControllerBuilder)
            ->setRequestVar($taxtribute->getMetaKey($name))
            ->setFormField($field)
            ->setDataManager($dataManager)
            ->addRule('valid_url', v::optional(v::url(false)), 'Enter Valid Url')
            ->addFilter('sanitize_text_field')
            ->create();

        $field = (new Field($controller))
            ->setRowPadding(1)
            ->setLabel($name)
            ->setConstraints(new Constrainer($attribute, $taxtribute));

        $manager->addField($controller);
        $section->addContent($attribute, $field);
    }
});
