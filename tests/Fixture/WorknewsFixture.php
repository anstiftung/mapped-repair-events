<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use App\Model\Entity\Worknews;

class WorknewsFixture extends AppFixture
{

    public function init(): void {
        $this->records = [
            [
                'workshop_uid' => 2,
                'email' => 'worknews-test@mailinator.com',
                'confirm' => Worknews::STATUS_OK,
            ],
            [
                'workshop_uid' => 2,
                'email' => 'worknews-test-1@mailinator.com',
                'confirm' => '07b9ec272178a9210f777beac7839a2f',
                'unsub' => '200f32888238b687f4b2232c3e124fd8',
                'created' => '2021-08-01 00:00:00',
            ],
            [
                'workshop_uid' => 100, // sic! does not exist
                'email' => 'worknews-test@mailinator.com',
                'confirm' => Worknews::STATUS_OK,
            ],
        ];
        parent::init();
    }

}
?>