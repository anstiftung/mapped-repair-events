<?php
declare(strict_types=1);
    echo $this->element('list',
       [
      'objects' => $objects,
      'heading' => 'Termine',
      'editMethod' => ['url' => 'urlEventEdit'],
      'showMethod' => ['url' => 'urlEventDetail'],
      'selectable' => false,
      'fields' => [
          ['name' => 'uid', 'label' => 'UID'],
          ['name' => 'image', 'label' => 'Bild'],
          ['name' => 'workshop.name', 'label' => 'Rep-Ini'],
          ['name' => 'eventbeschreibung', 'label' => 'Beschrei-bung', 'tooltip' => true],
          ['name' => 'datumstart', 'label' => 'Datum', 'template' => 'Events/date', 'sortable' => true],
          ['name' => 'series_uid', 'label' => 'TS', 'template' => 'Events/series', 'class' => 'icon'],
          ['name' => 'ort', 'label' => 'Ort'],
          ['name' => 'province.name', 'label' => 'Bundesland'],
          ['name' => 'owner_user.name', 'label' => 'Owner', 'sortable' => false],
          ['name' => 'created', 'type' => 'datetime', 'label' => 'erstellt'],
          ['name' => 'updated', 'type' => 'datetime', 'label' => 'geändert'],
        ],
      ]
    );
?>
