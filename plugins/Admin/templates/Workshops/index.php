<?php
declare(strict_types=1);
use Cake\Core\Configure;

echo $this->element('list', [
      'objects' => $objects,
      'heading' => 'Initiativen',
      'newMethod' => ['url' => 'urlWorkshopNew'],
      'editMethod' => ['url' => 'urlWorkshopEdit'],
      'showMethod' => ['url' => 'urlWorkshopDetail'],
      'optionalSearchForms' => [
          ['options' => $users, 'value' => 'Workshops.owner', 'label' => 'Owner'],
          ['options' => $users, 'value' => 'UsersWorkshops.user_uid', 'label' => 'Mitarbeiter']
      ],
      'emailFields' => [
        [
            'label' => 'E-Mails',
            'field' => 'email',
        ]
    ],
    'fields' => [
        ['name' => 'uid', 'label' => 'UID'],
        ['name' => 'image', 'label' => 'Bild'],
        ['name' => 'name', 'label' => 'Name'],
        ['name' => 'zip', 'label' => 'PLZ'],
        ['name' => 'city', 'label' => 'Stadt'],
        ['name' => 'street', 'label' => 'Anschrift'],
        ['name' => 'province.name', 'label' => 'Bundesland'],
        ['name' => 'country.code', 'label' => 'Land'],
        ['name' => 'users.name', 'type' => 'habtm', 'label' => 'Mitarbeiter'],
        ['name' => 'owner_user.name', 'label' => 'Owner', 'sortable' => false],
        ['name' => 'workshop_info_sheets_count', 'label' => 'LZ'],
        ['name' => 'worknews_count', 'label' => 'TA'],
        ['name' => 'created', 'type' => 'datetime', 'label' => 'erstellt'],
        ['name' => 'updated', 'type' => 'datetime', 'label' => 'geändert'],
        ['name' => 'email'],
        ['name' => 'website'],
        ['name' => 'twitter_username'],
        ['name' => 'facebook_username'],
        ['name' => 'feed_url']
    ]
]);
?>