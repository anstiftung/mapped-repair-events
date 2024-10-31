<?php
echo $this->element('list',
    [
        'objects' => $objects,
        'heading' => 'Förderanträge',
        'editMethod' => ['url' => 'urlFundingsAdminEdit'],
        'fields' => [
            ['name' => 'id', 'label' => 'ID'],
            ['name' => 'workshop.name', 'label' => 'Initiative'],
            ['name' => 'owner_user.name', 'label' => 'Owner', 'sortable' => false],
            ['name' => 'activity_proof_filename', 'type' => 'linkedUrl', 'linkedUrl' => 'urlFundingsActivityProofDetail', 'label' => 'Aktivitätsnachweis'],
            ['name' => 'activity_proof_ok', 'label' => 'AN OK'],
            ['name' => 'verified_fields_count',  'label' => 'verifziert'],
            ['name' => 'created', 'type' => 'datetime', 'label' => 'erstellt'],
            ['name' => 'modified', 'type' => 'datetime', 'label' => 'geändert'],
        ],
    ]
    );
?>
