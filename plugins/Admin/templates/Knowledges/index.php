<?php
declare(strict_types=1);
    echo $this->element('highlightNavi', ['main' => 'Reparaturwissen']);
    echo $this->element('list', [
       'objects' => $objects,
       'heading' => 'Reparaturwissen',
       'newMethod' => ['url' => 'urlKnowledgeNew'],
       'editMethod' => ['url' => 'urlKnowledgeEdit'],
       'showMethod' => ['url' => 'urlKnowledgeDetail'],
       'selectable' => false,
       'fields' => [
            ['name' => 'uid', 'label' => 'UID'],
            ['name' => 'title', 'label' => 'Titel'],
            ['name' => 'Categories.name', 'type' => 'habtm', 'label' => 'Kategorien'],
            ['name' => 'Skills.name', 'type' => 'habtm', 'label' => 'Kenntnisse'],
            ['name' => 'owner_user.name', 'label' => 'Owner'],
            ['name' => 'created', 'type' => 'datetime', 'label' => 'erstellt'],
            ['name' => 'updated', 'type' => 'datetime', 'label' => 'geändert'],
        ],
      ],
    );
?>