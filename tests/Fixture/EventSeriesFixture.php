<?php
declare(strict_types=1);

namespace App\Test\Fixture;

class EventSeriesFixture extends AppFixture
{
    public string $table = 'event_series';

    public function init(): void
    {
        $this->records = [];
        parent::init();
    }
}