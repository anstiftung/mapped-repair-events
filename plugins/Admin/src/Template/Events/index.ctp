<?php
    echo $this->element('list', 
       [
      'objects' => $objects
      ,'heading' => 'Termine'
      ,'editMethod' => ['url' => 'urlEventEdit']
      ,'showMethod' => ['url' => 'urlEventDetail']
      ,'fields' => [
         ['name' => 'uid', 'label' => 'UID']
        ,['name' => 'image', 'label' => 'Bild']
        ,['name' => 'workshop.name', 'label' => 'Rep-Ini']
        ,['name' => 'eventbeschreibung', 'label' => 'Beschrei-bung', 'tooltip' => true] 
        ,['name' => 'datumstart', 'type' => 'date', 'label' => 'Datum Start']
        ,['name' => 'uhrzeitstart', 'type' => 'time', 'label' => 'Uhrzeit Start']
        ,['name' => 'uhrzeitend', 'type' => 'time', 'label' => 'Uhrzeit Ende']
        ,['name' => 'ort', 'label' => 'Ort']
        ,['name' => 'owner_user.name', 'label' => 'Owner', 'sortable' => false]
        ,['name' => 'created', 'type' => 'datetime', 'label' => 'erstellt']
        ,['name' => 'updated', 'type' => 'datetime', 'label' => 'geändert']
        ]
      ]
    );
?>
