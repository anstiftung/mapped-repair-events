<?php
declare(strict_types=1);

echo $this->Html->link((string)$object->info_sheet_count, '/admin/info-sheets/index?' . http_build_query([
    'key-standard' => 'Brands.name',
    'val-standard' => $object->name,
]));
