<?php
declare(strict_types=1);

echo $this->element('list'
        ,[
            'objects' => $objects,
            'heading' => 'User',
            'editMethod' => ['url' => 'urlUserEdit'],
            'newMethod' => ['url' => 'urlUserNew'],
            'showMethod' => ['url' => 'urlUserProfile'],
            'selectable' => false,
            'optionalSearchForms' => [
                ['options' => $workshops, 'value' => 'UsersWorkshops.workshop_uid', 'label' => 'Mitarbeiter'],
            ],
            'emailFields' => [
                [
                    'label' => 'E-Mails',
                    'field' => 'email',
                ]
            ],
            'fields' => [
                ['name' => 'uid', 'label' => 'UID'],
                ['label' => 'Name', 'template' => 'list/users/name'],
                ['label' => 'Adresse', 'template' => 'list/users/address'],
                ['name' => 'Groups.name', 'type' => 'habtm', 'label' => 'Gruppen'],
                ['name' => 'owner_workshops.name', 'type' => 'habtm', 'label' => 'Initiative (Owner)'],
                ['name' => 'workshops.name', 'type' => 'habtm', 'label' => 'Initiative (Mitarbeiter)'],
                ['name' => 'created', 'type' => 'datetime', 'label' => 'erstellt'],
                ['name' => 'updated', 'type' => 'datetime', 'label' => 'geändert'],
                ['name' => 'email'],
                ['name' => 'website'],
            ]
        ]
    );
?>