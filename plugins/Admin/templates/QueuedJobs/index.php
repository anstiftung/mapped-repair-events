<?php
echo $this->element('list',
    [
        'objects' => $objects,
        'heading' => 'E-Mails',
        'hideDeleteLink' => true,
        'fields' => [
            ['name' => 'id', 'label' => 'ID'],
            ['label' => 'Message', 'template' => 'list/queuedJobs/message'],
            ['name' => 'created', 'type' => 'datetime', 'label' => 'erstellt'],
            ['name' => 'fetched', 'type' => 'datetime', 'label' => 'fetched'],
            ['name' => 'completed', 'type' => 'datetime', 'label' => 'completed'],
            ['name' => 'failure_message', 'label' => 'Fehler'],
        ],
    ]
);
?>
