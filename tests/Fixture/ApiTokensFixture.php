<?php
declare(strict_types=1);
namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class ApiTokensFixture extends TestFixture
{

    public const string VALID_TOKEN = 'valid-token-12345';
    public const string INACTIVE_TOKEN = 'inactive-token-12345';
    public const string EMPTY_SEARCH_TERMS_TOKEN = 'empty-search-terms-token';

    public function init(): void
        {
            $this->records = [
            [
                'id' => 1,
                'name' => 'Test Token',
                'token' => self::VALID_TOKEN,
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
                'token' => self::INACTIVE_TOKEN,
                'allowed_search_terms' => '["Berlin"]',
                'status' => 0,
                'last_used' => null,
                'expires_at' => null,
                'created' => '2026-01-01 00:00:00',
                'modified' => '2026-01-01 00:00:00',
            ],
            [
                'id' => 3,
                'name' => 'Empty Search Terms Token',
                'token' => self::EMPTY_SEARCH_TERMS_TOKEN,
                'allowed_search_terms' => '[]',
                'status' => 1,
                'last_used' => null,
                'expires_at' => null,
                'created' => '2026-01-01 00:00:00',
                'modified' => '2026-01-01 00:00:00',
            ],
        ];
        parent::init();
    }
}
