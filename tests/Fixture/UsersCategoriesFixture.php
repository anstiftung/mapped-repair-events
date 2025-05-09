<?php
declare(strict_types=1);

namespace App\Test\Fixture;

class UsersCategoriesFixture extends AppFixture
{

    public function init(): void {
        $this->records = [
            [
                'user_uid' => '2',
                'category_id' => '87',
            ],
            [
                'user_uid' => '1',
                'category_id' => '630',
            ],
        ];
        parent::init();
    }

}
?>