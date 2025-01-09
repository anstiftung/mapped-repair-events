<?php
declare(strict_types=1);
echo $this->element('list',
    [
        'objects' => $objects
        ,'heading' => 'Marken'
        ,'newMethod' => ['url' => 'urlBrandNew']
        ,'editMethod' => ['url' => 'urlBrandEdit']
        ,'hideDeleteLink' => true
        ,'fields' => [
            ['name' => 'id', 'label' => 'ID']
            ,['name' => 'name', 'label' => 'Marke']
            ,['name' => 'owner_user.name', 'label' => 'Owner']
            ,['name' => 'status', 'label' => 'Status']
            ,['name' => 'created', 'type' => 'datetime', 'label' => 'erstellt']
            ,['name' => 'modified', 'type' => 'datetime', 'label' => 'geändert']
        ]
    ]
    );
?>
