<?php
declare(strict_types=1);

namespace App\Test\Fixture;

class PagesFixture extends AppFixture
{

    public function init(): void {
        $this->records = [
            [
                'uid' => 5,
                'name' => 'Test Page',
                'text' => '<b>some html</b>',
                'url' => 'test-page',
                'status' => APP_ON
            ]
        ];
        parent::init();
    }

}
?>