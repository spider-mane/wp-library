<?php

use WebTheory\Saveyour\Contracts\FieldDataManagerInterface;
use WebTheory\Saveyour\Contracts\FormFieldControllerInterface;
use WebTheory\Saveyour\Controllers\FormFieldController;
use WebTheory\Saveyour\Field as BackalleyField;
use WebTheory\Saveyour\Fields\Checklist;
use WebTheory\Saveyour\Fields\Email;
use WebTheory\Saveyour\Fields\Input;
use WebTheory\Saveyour\Fields\Select;
use WebTheory\Saveyour\Fields\Tel;
use WebTheory\Saveyour\Groups\AddressMetaGroup;
use WebTheory\GuctilityBelt\Address\Address;
use WebTheory\GuctilityBelt\Address\GoogleGeocoder;
use WebTheory\GuctilityBelt\SelectOptions\UsStatesAndTerritories;
use WebTheory\Post2Post\TermRelatedPostsManager;
use WebTheory\Taxtrubute\TermBasedPostMeta;
use WebTheory\Leonidas\Fields\Managers\PostMetaFieldManager;
use WebTheory\Leonidas\MetaBox\Field;
use WebTheory\Leonidas\MetaBox\Fieldset;
use WebTheory\Leonidas\MetaBox\MetaBox;
use WebTheory\Leonidas\Fields\WpAdminField;
use WebTheory\Leonidas\Forms\Controllers\PostMetaBoxFormSubmissionManager;
use Respect\Validation\Validator as v;


########################################################################################################################
// add_action('parse_request', function ($request) {
//     exit(var_dump($request->query_vars, $request->request, $request));
// }, null, PHP_INT_MAX);


// $screen = get_current_screen();
// exit(var_dump($screen));
/**
 *
 */
$postType = 'ba_location';
$formController = new PostMetaBoxFormSubmissionManager($postType);


// $address = [
//     'street' => [
//         'label' => 'Street',
//         'type' => (new Input)
//             ->setId('ba-location--address--street')
//             ->setType('text')
//             ->addClass('regular-text'),
//         'data' => new PostMetaFieldManager('ba_location_address__street'),
//         'groups' => 'address'
//     ],
//     'city' => [
//         'label' => 'City',
//         'type' => (new Input)
//             ->setId('ba-location--address--city')
//             ->setType('text')
//             ->addClass('regular-text'),
//         'data' => new PostMetaFieldManager('ba_location_address__city'),
//         'groups' => 'address'
//     ],
//     'state' => [
//         'label' => 'State',
//         'type' => (new Select)
//             ->setId('ba-location--address--state')
//             ->addClass('regular-text')
//             ->setOptions(UsStatesAndTerritories::states('Select State')),
//         'data' => new PostMetaFieldManager('ba_location_address__state'),
//         'groups' => 'address'
//     ],
//     'zip' => [
//         'label' => 'Zip',
//         'type' => (new Input)
//             ->setId('ba-location--address--zip')
//             ->addClass('small-text')
//             ->setPlaceholder('Zip'),
//         'data' => new PostMetaFieldManager('ba_location_address__zip'),
//         'groups' => 'address'
//     ],
//     'complete' => [
//         'label' => 'Complete',
//         'type' => (new Input)
//             ->setId('ba-location--address--complete')
//             ->addClass('regular-text')
//             ->setPlaceholder('Formatted Address')
//             ->setDisabled(true),
//         'data' => (new PostMetaFieldManager('ba_location_address__complete')),
//     ],
//     'geo' => [
//         'label' => 'Geo Location',
//         'type' => (new Input)
//             ->setDisabled(true)
//             ->setName('geo')
//             ->setId('ba-location--address--geo')
//             ->addClass('regular-text'),
//         'data' => (new PostMetaFieldManager('ba_location_address__geo')),
//     ]
// ];

// $address = (new Fieldset('Address', $formController))->setFieldsDeprecated($address);

// $geo = (new PostMetaFieldManager('ba_location_address__geo'));
// $complete = (new PostMetaFieldManager('ba_location_address__complete'));
// $addressHelper = (new Address)->setGeocoder(new GoogleGeocoder('AIzaSyC-PMj5P8atDt61zPmdlCeTkVv4KaW-CiU'));
// $addressGeoGroup = (new AddressMetaGroup($addressHelper, $complete))->setGeoDataManager($geo);

// $fields = [
//     'street' => $controllers['street'],
//     'city' => $controllers['city'],
//     'state' => $controllers['state'],
//     'zip' => $controllers['zip'],
// ];

// foreach ($fields as $slug => $field) {
//     $addressGeoGroup->setField($slug, $field);
// }


$contactInfo = [
    'phone' => [
        'label' => 'Phone',
        'type' => (new Tel)
            ->setName('contact_info__phone')
            ->setId('ba-location--contact_info--phone')
            ->addClass('regular-text'),
        'data' => (new PostMetaFieldManager('ba_location_contact_info__phone'))
    ],
    'fax' => [
        'label' => 'Fax',
        'type' => (new Tel)
            ->setId('ba-location--contact_info--fax')
            ->addClass('regular-text')
            ->setName('contact_info__fax'),
        'data' => (new PostMetaFieldManager('ba_location_contact_info__fax')),
    ],
    'email' => [
        'label' => 'Email',
        'type' => (new Email)
            ->setName('contact_info__email')
            ->setId('ba-location--contact_info--email')
            ->addClass('regular-text'),
        'data' => (new PostMetaFieldManager('ba_location_contact_info__email')),
    ],
];

foreach ($contactInfo as $slug => &$unit) {
    $unit['field'] = (new WpAdminField("ba_{$slug}", $unit['type'], $unit['data']));
    unset($unit['type'], $unit['data']);
}


$contactInfo = (new Fieldset('Contact Information', $formController))->addFields($contactInfo);


// create metabox
$metabox = (new MetaBox('test', 'Test'))
    ->setScreen($postType)
    ->setContext('normal')
    // ->addContent('address', $address)
    ->addContent('contact_info', $contactInfo)
    ->hook();


$nonce = $metabox->getNonce();
$formController
    ->setNonce($nonce['name'], $nonce['action'])
    // ->addGroup('address', $addressGeoGroup)
    ->hook();

include 'field-grid.php';

$posts = get_posts([
    'post_type' => 'ba_menu_item',
    'posts_per_page' => -1,
    'orderby' => 'name',
    'order' => 'ASC',
]);


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


$taxonomy = 'ba_delivery_platforms';
$attribute = 'doordash';
$metaKey = "ba_location_delivery_platforms__{$attribute}";

$manager = new TermBasedPostMeta($metaKey, $taxonomy, $attribute);
$element = (new Input)->addClass('large-text')->setType('text');
$controller = (new WpAdminField('dp_doordash', $element, $manager));
$field = (new Field($controller))->setLabel('DoorDash');

$metabox->addContent('delivery_platform', $field);
$formController->addField($controller);
