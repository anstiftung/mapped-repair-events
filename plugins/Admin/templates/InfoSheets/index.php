<?php
declare(strict_types=1);
    echo $this->element('list',
       [
      'objects' => $objects
      ,'heading' => 'Laufzettel'
      ,'editMethod' => ['url' => 'urlInfoSheetEdit']
      ,'showMethod' => ['url' => 'urlWorkshopDetail']
      ,'fields' => [
         ['name' => 'uid', 'label' => 'UID']
        ,['name' => 'device_name', 'label' => 'Gerät']
        ,['name' => 'category.parent_category.name', 'label' => 'Oberkategorie']
        ,['name' => 'category.name', 'label' => 'Unterkategorie']
        ,['name' => 'category.status', 'label' => 'Kategorie-Status']
        ,['name' => 'brand.name', 'label' => 'Marke']
        ,['name' => 'brand.status', 'label' => 'Marken-Status']
        ,['name' => 'event.workshop.name', 'label' => 'Initiative']
        ,['name' => 'event.uid', 'label' => 'Event-UID']
        ,['name' => 'owner_user.name', 'label' => 'Owner', 'sortable' => false]
        ,['name' => 'created', 'type' => 'datetime', 'label' => 'erstellt']
        ,['name' => 'updated', 'type' => 'datetime', 'label' => 'geändert']
        ]
      ]
    );
?>
