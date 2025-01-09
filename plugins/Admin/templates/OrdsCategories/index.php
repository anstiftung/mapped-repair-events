<?php
declare(strict_types=1);
echo $this->element('list',
    [
        'objects' => $objects
        ,'heading' => 'ORDS-Kategorien'
        ,'newMethod' => ['url' => 'urlOrdsCategoryNew']
        ,'editMethod' => ['url' => 'urlOrdsCategoryEdit']
        ,'hideDeleteLink' => true
        ,'fields' => [
            ['name' => 'id', 'label' => 'ID']
            ,['name' => 'name', 'label' => 'Unterkategorie']
            ,['name' => 'owner_user.name', 'label' => 'Owner']
            ,['name' => 'status', 'label' => 'Status']
            ,['name' => 'created', 'type' => 'datetime', 'label' => 'erstellt']
            ,['name' => 'modified', 'type' => 'datetime', 'label' => 'geändert']
        ]
    ]
    );
?>
