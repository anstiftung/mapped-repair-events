<?php
declare(strict_types=1);
namespace App\Test\TestCase\Model\Entity;

use App\Model\Entity\ApiToken;
use App\Test\TestCase\AppTestCase;

class ApiTokenTest extends AppTestCase
{
    public function testIsDomainAllowedWithValidDomain(): void
    {
        $apiToken = new ApiToken([
            'allowed_domains' => '["example.org", "example.com"]',
        ]);

        $this->assertTrue($apiToken->isDomainAllowed('example.org'));
        $this->assertTrue($apiToken->isDomainAllowed('example.com'));
    }

    public function testIsDomainAllowedWithInvalidDomain(): void
    {
        $apiToken = new ApiToken([
            'allowed_domains' => '["example.org", "example.com"]',
        ]);

        $this->assertFalse($apiToken->isDomainAllowed('invalid-domain.com'));
    }

    public function testIsDomainAllowedCaseInsensitive(): void
    {
        $apiToken = new ApiToken([
            'allowed_domains' => '["example.org", "Example.COM"]',
        ]);

        $this->assertTrue($apiToken->isDomainAllowed('EXAMPLE.ORG'));
        $this->assertTrue($apiToken->isDomainAllowed('example.com'));
        $this->assertTrue($apiToken->isDomainAllowed('EXAMPLE.com'));
    }

    public function testIsDomainAllowedWithEmptyDomains(): void
    {
        $apiToken = new ApiToken([
            'allowed_domains' => '[]',
        ]);

        $this->assertFalse($apiToken->isDomainAllowed('example.org'));
    }

    public function testIsDomainAllowedWithNullDomains(): void
    {
        $apiToken = new ApiToken([
            'allowed_domains' => null,
        ]);

        $this->assertFalse($apiToken->isDomainAllowed('example.org'));
    }

    public function testIsDomainAllowedWithArrayDomains(): void
    {
        $apiToken = new ApiToken([
            'allowed_domains' => ['example.org', 'example.com'],
        ]);

        $this->assertTrue($apiToken->isDomainAllowed('example.org'));
        $this->assertTrue($apiToken->isDomainAllowed('example.com'));
    }

    public function testIsDomainAllowedWithDefaultAllowedDomain(): void
    {
        $apiToken = new ApiToken([
            'allowed_domains' => '["example.org"]',
        ]);
        $this->assertTrue($apiToken->isDomainAllowed('anstiftung.github.io'));
    }

    public function testIsDomainAllowedWithDefaultAllowedDomainEvenWhenEmpty(): void
    {
        $apiToken = new ApiToken([
            'allowed_domains' => '[]',
        ]);

        $this->assertTrue($apiToken->isDomainAllowed('anstiftung.github.io'));
    }
}
