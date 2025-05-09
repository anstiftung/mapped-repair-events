<?php
declare(strict_types=1);

namespace App\Test\Fixture;

class BlogsFixture extends AppFixture
{

    public function init(): void
    {
        $this->records = [
            [
                'id' => 1,
                'name' => 'Neuigkeiten',
                'url' => 'neuigkeiten'
            ]
        ];
        parent::init();
    }

}
?>