<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class EventSeries extends BaseMigration
{
    public function change(): void
    {
        $this->table('event_series', ['id' => false, 'primary_key' => ['uid']])
            ->addColumn('uid', 'string', ['limit' => 36])
            ->addColumn('workshop_uid', 'integer', ['signed' => false])
            ->addColumn('parent_uid', 'string', ['limit' => 36, 'null' => true])
            ->addColumn('settings', 'text')
            ->addColumn('timezone', 'string', ['limit' => 64])
            ->addColumn('created', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addIndex(['workshop_uid'])
            ->create();

        $this->table('events')
            ->addColumn('series_uid', 'string', ['limit' => 36, 'null' => true])
            ->addColumn('series_date', 'date', ['null' => true])
            ->addColumn('series_overrides', 'text', ['null' => true])
            ->addIndex(['series_uid', 'series_date'], ['unique' => true])
            ->update();
    }
}