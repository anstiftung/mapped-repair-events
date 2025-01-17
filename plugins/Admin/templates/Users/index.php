<?php

use Cake\Core\Configure;

echo $this->element('list'
        ,[
            'objects' => $objects,
            'heading' => 'User',
            'editMethod' => ['url' => 'urlUserEdit'],
            'newMethod' => ['url' => 'urlUserNew'],
            'showMethod' => ['url' => 'urlUserProfile'],
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
                ['name' => 'nick', 'label' => 'Nick'],
                ['name' => 'firstname', 'label' => 'Vorname'],
                ['name' => 'lastname', 'label' => 'Nachname'],
                ['name' => 'city', 'label' => 'Stadt'],
                ['name' => 'province.name', 'label' => 'Bundesland'],
                ['name' => 'country_code', 'label' => 'Land'],
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