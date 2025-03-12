<?php
declare(strict_types=1);

    echo $this->element('list', [
      'objects' => $objects,
      'heading' => 'Seiten',
      'newMethod' => ['url' => 'urlPageNew'],
      'editMethod' => ['url' => 'urlPageEdit'],
      'showMethod' => ['url' => 'urlPageDetail'],
      'selectable' => false,
      'fields' => [
          ['name' => 'uid', 'label' => 'UID'],
          ['name' => 'name', 'label' => 'Name'],
          ['name' => 'url', 'label' => 'Url'],
          ['name' => 'menu_type', 'label' => 'Menü-Typ'],
          ['name' => 'parent_page.name', 'label' => 'Parent'],
          ['name' => 'position', 'label' => 'Position'],
          ['name' => 'owner_user.name', 'label' => 'Owner'],
          ['name' => 'created', 'type' => 'datetime', 'label' => 'erstellt'],
          ['name' => 'updated', 'type' => 'datetime', 'label' => 'geändert'],
      ]
     ]);
?>