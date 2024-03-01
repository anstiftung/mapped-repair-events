<?php
echo $this->element('list',
    [
        'objects' => $objects,
        'heading' => 'Abos',
        'fields' => [
            ['name' => 'id', 'label' => 'ID'],
            ['name' => 'email', 'type' => 'unchanged', 'label' => 'E-Mail'],
            ['name' => 'workshop.name', 'label' => 'Initiative'],
            ['name' => 'confirm', 'label' => 'Bestätigung'],
            ['name' => 'created', 'type' => 'datetime', 'label' => 'erstellt'],
            ['name' => 'modified', 'type' => 'datetime', 'label' => 'geändert'],
        ],
    ]
    );
?>
