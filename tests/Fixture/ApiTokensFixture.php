<?php
declare(strict_types=1);
namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class ApiTokensFixture extends TestFixture
{

    public function init(): void
        {
            $this->records = [
            [
                'id' => 1,
                'name' => 'Test Token',
                'token' => 'valid-token-12345',
                'allowed_search_terms' => '["Berlin", "München"]',
                'status' => 1,
                'last_used' => null,
                'expires_at' => null,
                'created' => '2026-01-01 00:00:00',
                'modified' => '2026-01-01 00:00:00',
            ],
            [
                'id' => 2,
                'name' => 'Inactive Token',
                'token' => 'inactive-token-12345',
                'allowed_search_terms' => null,
                'status' => 0,
                'last_used' => null,
                'expires_at' => null,
                'created' => '2026-01-01 00:00:00',
                'modified' => '2026-01-01 00:00:00',
            ],
        ];
        parent::init();
    }
}
