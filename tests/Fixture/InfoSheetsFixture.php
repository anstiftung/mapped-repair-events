<?php
declare(strict_types=1);

namespace App\Test\Fixture;

class InfoSheetsFixture extends AppFixture
{

    public function init(): void {
        $this->records = [
            [
                'uid' => 7,
                'status' => APP_ON,
                'event_uid' => 6,
                'category_id' => 87,
            ], 
        ];
        parent::init();
    }
}
?>