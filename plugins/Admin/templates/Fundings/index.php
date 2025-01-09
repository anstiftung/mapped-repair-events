<?php
declare(strict_types=1);
echo $this->element('list',
    [
        'objects' => $objects,
        'heading' => 'Förderanträge',
        'editMethod' => ['url' => 'urlFundingsAdminEdit'],
        'deleteMethod' => '/admin/intern/ajaxDeleteFunding',
        'optionalSearchForms' => [
            ['options' => $fundingStatus, 'value' => 'FundingStatus', 'label' => 'Status'],
        ],
          'fields' => [
            ['name' => 'uid', 'label' => 'UID'],
            ['name' => 'workshop.name', 'label' => 'Initiative', 'sortable' => false],
            ['label' => 'AN', 'template' => 'list/fundings/activityProof'],
            ['label' => 'FB', 'template' => 'list/fundings/freistellungsbescheid'],
            ['label' => 'bestätigte Felder', 'template' => 'list/fundings/verifiedFields'],
            ['name' => 'owner_user.name', 'label' => 'Owner', 'sortable' => false],
            ['name' => 'fundingsupporter.name', 'label' => 'Träger', 'sortable' => false],
            ['label' => 'Summe', 'template' => 'list/fundings/budgetplan', 'sortable' => false],
            ['label' => 'eingereicht', 'template' => 'list/fundings/submitInfo'],
            ['label' => 'ZB', 'template' => 'list/fundings/zuwendungsbestaetigung'],
            ['label' => 'überwiesen', 'template' => 'list/fundings/moneyTransferInfo'],
            ['name' => 'created', 'type' => 'datetime', 'label' => 'erstellt'],
            ['name' => 'modified', 'type' => 'datetime', 'label' => 'geändert'],
        ],
    ]
    );
?>
