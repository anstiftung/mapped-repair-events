<?php
declare(strict_types=1);
echo $this->element('list',
    [
        'objects' => $objects,
        'heading' => 'E-Mails',
        'hideDeleteLink' => true,
        'selectable' => false,
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
