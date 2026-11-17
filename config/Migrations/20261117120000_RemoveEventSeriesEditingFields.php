<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class RemoveEventSeriesEditingFields extends BaseMigration
{
    public function up(): void
    {
        $this->table('event_series')
            ->removeColumn('parent_uid')
            ->update();
        $this->table('events')
            ->removeIndex(['series_uid', 'series_date'])
            ->removeColumn('series_date')
            ->removeColumn('series_overrides')
            ->update();
    }

    public function down(): void
    {
        $this->table('event_series')
            ->addColumn('parent_uid', 'string', ['limit' => 36, 'null' => true])
            ->update();
        $this->table('events')
            ->addColumn('series_date', 'date', ['null' => true])
            ->addColumn('series_overrides', 'text', ['null' => true])
            ->addIndex(['series_uid', 'series_date'])
            ->update();
        $this->execute('UPDATE events SET series_date = DATE(datumstart) WHERE series_uid IS NOT NULL');
    }
}