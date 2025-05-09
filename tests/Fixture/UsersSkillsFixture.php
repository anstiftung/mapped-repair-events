<?php
declare(strict_types=1);

namespace App\Test\Fixture;

class UsersSkillsFixture extends AppFixture
{

    public function init(): void {
        $this->records = [
            [
                'user_uid' => '1',
                'skill_id' => '1'
            ],
        ];
        parent::init();
    }

}
?>