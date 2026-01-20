<?php
declare(strict_types=1);
namespace App\Test\TestCase\Model\Table;

use App\Model\Table\ApiTokensTable;
use App\Test\TestCase\AppTestCase;

class ApiTokensTableTest extends AppTestCase
{
    protected ApiTokensTable $ApiTokens;

    public function setUp(): void
    {
        parent::setUp();
        /** @var ApiTokensTable $ApiTokens */
        $this->ApiTokens = $this->getTableLocator()->get('ApiTokens');
    }

    public function testValidationDefault(): void
    {
        $apiToken = $this->ApiTokens->newEntity([
            'name' => 'Test Token',
            'token' => 'test-token-123456789',
            'status' => true,
        ]);

        $this->assertEmpty($apiToken->getErrors());
    }

    public function testValidationNameRequired(): void
    {
        $apiToken = $this->ApiTokens->newEntity([
            'token' => 'test-token-123456789',
            'status' => true,
        ]);

        $this->assertNotEmpty($apiToken->getErrors());
        $this->assertArrayHasKey('name', $apiToken->getErrors());
    }

    public function testFindByTokenValid(): void
    {
        $token = $this->ApiTokens->findByToken('valid-token-12345');
        $this->assertNotNull($token);
        $this->assertEquals('Test Token', $token->name);
    }

    public function testFindByTokenInvalid(): void
    {
        $token = $this->ApiTokens->findByToken('non-existent-token');
        $this->assertNull($token);
    }

    public function testFindByTokenInactive(): void
    {
        $token = $this->ApiTokens->findByToken('inactive-token-12345');
        $this->assertNull($token);
    }
}
