<?php

declare(strict_types=1);

use Migrations\AbstractMigration;

class Fundingconfirmedevents extends AbstractMigration
{
    public function change(): void
    {
        $this->table('fundingconfirmedevents')
        ->addColumn('event_uid', 'integer', [
            'null' => false,
            'signed' => false,
        ])
        ->addColumn('funding_uid', 'integer', [
            'null' => false,
            'signed' => false,
        ])
        ->addColumn('created', 'datetime', [
            'default' => 'CURRENT_TIMESTAMP',
            'null' => false,
        ])
        ->addIndex('event_uid', [
            'unique' => true,
        ])
        ->create();
    }
}
