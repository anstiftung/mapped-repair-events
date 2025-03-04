<?php
declare(strict_types=1);

namespace App\Test\Fixture;

class EventCategoriesFixture extends AppFixture
{

    public function init(): void
    {
        $this->records = [
            [
                'event_uid' => 6,
                'category_id' => 87,
            ]
        ];
        parent::init();
    }

}
?>