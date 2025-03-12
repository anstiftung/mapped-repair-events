<?php
declare(strict_types=1);
echo $this->element('list',
    [
        'objects' => $objects,
        'heading' => 'Abos',
        'selectable' => false,
        'fields' => [
            ['name' => 'id', 'label' => 'ID'],
            ['name' => 'email', 'type' => 'unchanged', 'label' => 'E-Mail'],
            ['name' => 'workshop.name', 'label' => 'Initiative', 'filterParam' => 'Worknews.workshop_uid'],
            ['name' => 'worknews_count', 'label' => 'Termin-Abos'],
            ['name' => 'confirm', 'label' => 'Bestätigung'],
            ['name' => 'activation_email_resent', 'type' => 'datetime', 'label' => 'Link erneut gesendet'],
            ['name' => 'created', 'type' => 'datetime', 'label' => 'erstellt'],
            ['name' => 'modified', 'type' => 'datetime', 'label' => 'geändert'],
        ],
    ]
    );
?>
