<?php
declare(strict_types=1);

namespace App\Test\Fixture;

class SkillsFixture extends AppFixture
{

    public function init(): void {
        $this->records = [
           [
              'id' => 1,
              'name' => 'Open Source',
              'status' => 1,
           ]
        ];
        parent::init();
    }

}
?>