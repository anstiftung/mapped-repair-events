<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class EventSeriesPositions extends BaseMigration
{
    public function up(): void
    {
        $this->table('events')
            ->addColumn('series_position', 'integer', ['null' => true, 'signed' => false])
            ->removeIndex(['series_uid', 'series_date'])
            ->update();
        $this->execute('UPDATE events INNER JOIN (
            SELECT uid, ROW_NUMBER() OVER (PARTITION BY series_uid ORDER BY series_date, uid) - 1 AS position
            FROM events WHERE series_uid IS NOT NULL
        ) AS positions ON events.uid = positions.uid SET events.series_position = positions.position');
        $this->table('events')
            ->addIndex(['series_uid', 'series_position'], ['unique' => true])
            ->addIndex(['series_uid', 'series_date'])
            ->update();
    }

    public function down(): void
    {
        $this->table('events')
            ->removeIndex(['series_uid', 'series_position'])
            ->removeColumn('series_position')
            ->update();
    }
}