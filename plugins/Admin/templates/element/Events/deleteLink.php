<?php
declare(strict_types=1);

/** @var \App\Model\Entity\Event $object */

echo $this->Html->link(
    '<i class="far fa-trash-alt fa-border"></i>',
    'javascript:void(0);',
    [
        'class' => 'delete-event',
        'data-event-uid' => $object->uid,
        'data-event-series' => !empty($object->series_uid) ? '1' : '0',
        'title' => 'löschen',
        'escape' => false,
    ]
);
