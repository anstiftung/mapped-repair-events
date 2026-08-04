<?php
declare(strict_types=1);

if ($object->worknews_count > 0) {
    echo $this->Html->link((string)$object->worknews_count, '/admin/worknews/index?' . http_build_query([
        'key-standard' => 'Worknews.workshop_uid',
        'val-standard' => $object->uid,
    ]));
}
