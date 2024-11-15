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
            ['name' => 'workshop.funding_activity_proof_required', 'label' => 'Aktivitätsnachweis notwendig?'],
            ['name' => 'activity_proofs_count', 'label' => 'Aktivitätsnachweise'],
            ['name' => 'verified_fields_count',  'label' => 'bestätigte Felder'],
            ['name' => 'owner_user.name', 'label' => 'Owner', 'sortable' => false],
            ['name' => 'created', 'type' => 'datetime', 'label' => 'erstellt'],
            ['name' => 'modified', 'type' => 'datetime', 'label' => 'geändert'],
        ],
    ]
    );
?>
