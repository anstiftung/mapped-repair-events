<?php
echo $this->element('list',
    [
        'objects' => $objects,
        'heading' => 'E-Mails',
        'hideDeleteLink' => true,
        'fields' => [
            ['name' => 'id', 'label' => 'ID'],
            ['label' => 'An', 'template' => 'list/queuedJobs/to'],
            ['label' => 'Message', 'template' => 'list/queuedJobs/message'],
            ['label' => 'Attachments', 'template' => 'list/queuedJobs/attachments'],
            ['name' => 'created', 'type' => 'datetimeWithSeconds', 'label' => 'erstellt'],
            ['name' => 'fetched', 'type' => 'datetimeWithSeconds', 'label' => 'fetched'],
            ['name' => 'completed', 'type' => 'datetimeWithSeconds', 'label' => 'completed'],
            ['name' => 'failure_message', 'label' => 'Fehler'],
        ],
    ]
);
?>
