<?php
declare(strict_types=1);
use Cake\Core\Configure;

echo $this->element('list', [
      'objects' => $objects,
      'heading' => 'Initiativen',
      'newMethod' => ['url' => 'urlWorkshopNew'],
      'editMethod' => ['url' => 'urlWorkshopEdit'],
      'showMethod' => ['url' => 'urlWorkshopDetail'],
      'selectable' => false,
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
        ['label' => 'Adresse', 'template' => 'list/workshops/address'],
        ['name' => 'users.name', 'type' => 'habtm', 'label' => 'Mitarbeiter'],
        ['name' => 'owner_user.name', 'label' => 'Owner', 'sortable' => false],
        ['name' => 'workshop_info_sheets_count', 'label' => 'LZ'],
        ['name' => 'worknews_count', 'label' => 'TA'],
        ['name' => 'created', 'type' => 'datetime', 'label' => 'erstellt'],
        ['name' => 'updated', 'type' => 'datetime', 'label' => 'geändert'],
        ['name' => 'email'],
    ]
]);
?>