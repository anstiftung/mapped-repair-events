<?php
declare(strict_types=1);

namespace App\Test\Fixture;

class WorkshopsCategoriesFixture extends AppFixture
{

    public function init(): void {
        $this->records = [
            [
                'workshop_uid' => '2',
                'category_id' => '630',
            ],
        ];
        parent::init();
    }

}
?>