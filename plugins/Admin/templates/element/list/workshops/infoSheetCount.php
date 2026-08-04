<?php
declare(strict_types=1);

if ($object->workshop_info_sheets_count > 0) {
    echo $this->Html->link((string)$object->workshop_info_sheets_count, '/admin/info-sheets/index?' . http_build_query([
        'key-standard' => 'Events.workshop_uid',
        'val-standard' => $object->uid,
    ]));
}
