<?php
declare(strict_types=1);

echo $this->Html->link((string)$object->event->workshop->name, '/admin/info-sheets/index?' . http_build_query([
    'key-standard' => 'Events.workshop_uid',
    'val-standard' => $object->event->workshop->uid,
]));

