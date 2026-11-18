<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class EventSeries extends BaseMigration
{
    public function change(): void
    {
        $this->table('event_series', ['id' => false, 'primary_key' => ['uid']])
            ->addColumn('uid', 'string', ['limit' => 36])
            ->addColumn('start_date', 'date')
            ->addColumn('end_date', 'date')
            ->addColumn('frequency', 'string', ['limit' => 7])
            ->addColumn('repeat_interval', 'integer', ['limit' => 1])
            ->addColumn('monthly_position', 'integer', ['limit' => 1, 'null' => true])
            ->addColumn('monthly_weekday', 'string', ['limit' => 2, 'null' => true])
            ->addColumn('repeat_monday', 'boolean', ['default' => false])
            ->addColumn('repeat_tuesday', 'boolean', ['default' => false])
            ->addColumn('repeat_wednesday', 'boolean', ['default' => false])
            ->addColumn('repeat_thursday', 'boolean', ['default' => false])
            ->addColumn('repeat_friday', 'boolean', ['default' => false])
            ->addColumn('repeat_saturday', 'boolean', ['default' => false])
            ->addColumn('repeat_sunday', 'boolean', ['default' => false])
            ->addColumn('created', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->create();

        $this->table('events')
            ->addColumn('series_uid', 'string', ['limit' => 36, 'null' => true])
            ->addIndex(['series_uid', 'datumstart'])
            ->update();
    }
}