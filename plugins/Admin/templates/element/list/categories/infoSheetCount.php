<?php
declare(strict_types=1);

if ($object->info_sheet_count > 0) {
    echo $this->Html->link((string)$object->info_sheet_count, '/admin/info-sheets/index?' . http_build_query([
        'key-standard' => 'Categories.name',
        'val-standard' => $object->name,
    ]));
}
