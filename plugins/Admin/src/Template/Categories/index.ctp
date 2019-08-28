<?php
echo $this->element('list',
    [
        'objects' => $objects
        ,'heading' => 'Kategorien'
        ,'newMethod' => ['url' => 'urlCategoryNew']
        ,'editMethod' => ['url' => 'urlCategoryEdit']
        ,'hideDeleteLink' => true
        ,'fields' => [
            ['name' => 'id', 'label' => 'ID']
            ,['name' => 'parent_category.name', 'label' => 'Oberkategorie']
            ,['name' => 'name', 'label' => 'Unterkategorie']
            ,['name' => 'visible_on_platform', 'label' => 'Sichtbar']
            ,['name' => 'carbon_footprint', 'label' => 'CF']
            ,['name' => 'material_footprint', 'label' => 'MF']
            ,['name' => 'owner_user.name', 'label' => 'Owner']
            ,['name' => 'status', 'label' => 'Status']
            ,['name' => 'created', 'type' => 'datetime', 'label' => 'erstellt']
            ,['name' => 'modified', 'type' => 'datetime', 'label' => 'geändert']
        ]
    ]
    );
?>
