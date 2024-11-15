<?php
echo $this->element('list',
    [
        'objects' => $objects,
        'heading' => 'Förderanträge',
        'editMethod' => ['url' => 'urlFundingsAdminEdit'],
        'deleteMethod' => '/admin/intern/ajaxDeleteFunding',
        'fields' => [
            ['name' => 'uid', 'label' => 'UID'],
            ['name' => 'workshop.name', 'label' => 'Initiative'],
            ['label' => 'Aktivitätsnachweis notwendig?', 'template' => 'list/fundings/activityProof'],
            ['label' => 'bestätigte Felder', 'template' => 'list/fundings/verifiedFields'],
            ['name' => 'owner_user.name', 'label' => 'Owner', 'sortable' => false],
            ['name' => 'created', 'type' => 'datetime', 'label' => 'erstellt'],
            ['name' => 'modified', 'type' => 'datetime', 'label' => 'geändert'],
        ],
    ]
    );
?>
