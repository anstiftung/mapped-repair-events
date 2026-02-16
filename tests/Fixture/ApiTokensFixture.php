<?php
declare(strict_types=1);
namespace App\Test\Fixture;

use App\Model\Entity\ApiToken;
use Cake\TestSuite\Fixture\TestFixture;

class ApiTokensFixture extends TestFixture
{

    public const string VALID_WORKSHOPS_TOKEN = 'valid-token-12345';
    public const string INACTIVE_WORKSHOPS_TOKEN = 'inactive-token-12345';
    public const string EMPTY_WORKSHOPS_SEARCH_TERMS_TOKEN = 'empty-search-terms-token';
    public const string WRONG_DOMAIN_WORKSHOPS_TOKEN = 'wrong-domain-token-12345';
    public const string VALID_SPLITTER_TOKEN = 'valid-splitter-token-12345';
    public const string VALID_WORKSHOPS_HYPERMODE_TOKEN = 'valid-workshops-hypermode-token-12345';

    public function init(): void
    {
        $this->records = [
            [
                'id' => 1,
                'name' => 'Test Token',
                'type' => ApiToken::TYPE_WORKSHOPS,
                'token' => self::VALID_WORKSHOPS_TOKEN,
                'allowed_search_terms' => '["Berlin", "München"]',
                'allowed_domains' => '["localhost"]',
                'status' => 1,
                'last_used' => null,
                'expires_at' => null,
                'created' => '2026-01-01 00:00:00',
                'modified' => '2026-01-01 00:00:00',
            ],
            [
                'id' => 2,
                'name' => 'Inactive Token',
                'type' => ApiToken::TYPE_WORKSHOPS,
                'token' => self::INACTIVE_WORKSHOPS_TOKEN,
                'allowed_search_terms' => '["Berlin"]',
                'allowed_domains' => '["localhost"]',
                'status' => 0,
                'last_used' => null,
                'expires_at' => null,
                'created' => '2026-01-01 00:00:00',
                'modified' => '2026-01-01 00:00:00',
            ],
            [
                'id' => 3,
                'name' => 'Empty Search Terms Token',
                'type' => ApiToken::TYPE_WORKSHOPS,
                'token' => self::EMPTY_WORKSHOPS_SEARCH_TERMS_TOKEN,
                'allowed_search_terms' => '[]',
                'allowed_domains' => '["localhost"]',
                'status' => 1,
                'last_used' => null,
                'expires_at' => null,
                'created' => '2026-01-01 00:00:00',
                'modified' => '2026-01-01 00:00:00',
            ],
            [
                'id' => 4,
                'name' => 'Wrong Domain Token',
                'type' => ApiToken::TYPE_WORKSHOPS,
                'token' => self::WRONG_DOMAIN_WORKSHOPS_TOKEN,
                'allowed_search_terms' => '["Berlin"]',
                'allowed_domains' => '["example.com"]',
                'status' => 1,
                'last_used' => null,
                'expires_at' => null,
                'created' => '2026-01-01 00:00:00',
                'modified' => '2026-01-01 00:00:00',
            ],
[
                'id' => 5,
                'name' => 'Test Splitter Token',
                'type' => ApiToken::TYPE_SPLITTER,
                'token' => self::VALID_SPLITTER_TOKEN,
                'allowed_search_terms' => '[]',
                'allowed_domains' => '["localhost"]',
                'status' => 1,
                'last_used' => null,
                'expires_at' => null,
                'created' => '2026-01-01 00:00:00',
                'modified' => '2026-01-01 00:00:00',
            ],
            [
                'id' => 6,
                'name' => 'Test Workshops HyperMode Token',
                'type' => ApiToken::TYPE_HYPERMODE_WEBSITE,
                'token' => self::VALID_WORKSHOPS_HYPERMODE_TOKEN,
                'allowed_search_terms' => '[]',
                'allowed_domains' => '["localhost"]',
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
