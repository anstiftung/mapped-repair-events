<?php
echo $this->element('list',
    [
        'objects' => $objects
        ,'heading' => 'Kenntnisse'
        ,'newMethod' => ['url' => 'urlSkillNew']
        ,'editMethod' => ['url' => 'urlSkillEdit']
        ,'hideDeleteLink' => true
        ,'fields' => [
            ['name' => 'id', 'label' => 'ID']
            ,['name' => 'name', 'label' => 'Name']
            ,['name' => 'owner_user.name', 'label' => 'Owner']
            ,['name' => 'status', 'label' => 'Status']
            ,['name' => 'created', 'type' => 'datetime', 'label' => 'erstellt']
            ,['name' => 'modified', 'type' => 'datetime', 'label' => 'geändert']
        ]
    ]
    );
?>
