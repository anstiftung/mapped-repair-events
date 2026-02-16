<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Test\Fixture\ApiTokensFixture;
use App\Test\TestCase\AppTestCase;
use Cake\I18n\Date;

class ApiControllerTest extends AppTestCase {

    public function testRestWorkshopsBerlin(): void
    {
        $this->configRequest([
            'headers' => [
                'Authorization' => 'Bearer ' . ApiTokensFixture::VALID_TOKEN,
                'Origin' => 'http://localhost',
            ],
        ]);
        $expectedResult = file_get_contents(TESTS . 'comparisons' . DS . 'rest-workshops-berlin.json');
        $expectedResult = $this->correctServerName($expectedResult);
        $expectedNextEventDate = Date::now()->addDays(7)->format('d.m.Y');
        $expectedResult = $this->correctExpectedDate($expectedResult, $expectedNextEventDate);
        $this->get('/api/v1/workshops?city=berlin');
        $this->assertResponseContains($expectedResult);
        $this->assertResponseOk();
    }

    public function testRestWorkshopsHamburg(): void
    {
        $this->configRequest([
            'headers' => [
                'Authorization' => 'Bearer ' . ApiTokensFixture::VALID_TOKEN,
                'Origin' => 'http://localhost',
            ],
        ]);
        $this->get('/api/v1/workshops?city=hamburg');
        $this->assertResponseCode(401);
        $response = $this->getJsonResponseBody();
        $this->assertEquals('Access to this city is not allowed with this API token. Allowed search terms: Berlin, München', $response['error']);
    }

    public function testRestWorkshopsWrongParam(): void
    {
        $this->configRequest([
            'headers' => [
                'Authorization' => 'Bearer ' . ApiTokensFixture::VALID_TOKEN,
                'Origin' => 'http://localhost',
            ],
        ]);
        $this->get('/api/v1/workshops?city=ha');
        $this->assertResponseCode(401);
        $response = $this->getJsonResponseBody();
        $this->assertEquals('Access to this city is not allowed with this API token. Allowed search terms: Berlin, München', $response['error']);
    }
    public function testApiV1WorkshopsWithoutToken(): void
    {
        $this->get('/api/v1/workshops?city=berlin');
        $this->assertResponseCode(401);
        $response = $this->getJsonResponseBody();
        $this->assertEquals('API token is required. Please provide a valid token in the Authorization header as Bearer token.', $response['error']);
    }

    public function testApiV1WorkshopsWithInvalidToken(): void
    {
        $this->configRequest([
            'headers' => [
                'Authorization' => 'Bearer invalid-token-12345',
                'Origin' => 'http://localhost',
            ],
        ]);
        $this->get('/api/v1/workshops?city=berlin');
        $this->assertResponseCode(401);
        $response = $this->getJsonResponseBody();
        $this->assertEquals('Invalid or inactive API token', $response['error']);
    }

    public function testApiV1WorkshopsWithInactiveToken(): void
    {
        $this->configRequest([
            'headers' => [
                'Authorization' => 'Bearer ' . ApiTokensFixture::INACTIVE_TOKEN,
                'Origin' => 'http://localhost',
            ],
        ]);
        $this->get('/api/v1/workshops?city=berlin');
        $this->assertResponseCode(401);
        $response = $this->getJsonResponseBody();
        $this->assertEquals('Invalid or inactive API token', $response['error']);
    }

    public function testApiV1WorkshopsWithTokenRequestingNonValidCity(): void
    {
        $this->configRequest([
            'headers' => [
                'Authorization' => 'Bearer ' . ApiTokensFixture::VALID_TOKEN,
                'Origin' => 'http://localhost',
            ],
        ]);
        $this->get('/api/v1/workshops?city=köln');
        $this->assertResponseCode(401);
        $response = $this->getJsonResponseBody();
        $this->assertEquals('Access to this city is not allowed with this API token. Allowed search terms: Berlin, München', $response['error']);
    }

    public function testApiV1WorkshopsWithBearerToken(): void
    {
        $this->configRequest([
            'headers' => [
                'Authorization' => 'Bearer ' . ApiTokensFixture::VALID_TOKEN,
                'Origin' => 'http://localhost',
            ],
        ]);
        $expectedResult = file_get_contents(TESTS . 'comparisons' . DS . 'rest-workshops-berlin.json');
        $expectedResult = $this->correctServerName($expectedResult);
        $expectedNextEventDate = Date::now()->addDays(7)->format('d.m.Y');
        $expectedResult = $this->correctExpectedDate($expectedResult, $expectedNextEventDate);
        $this->get('/api/v1/workshops?city=berlin');
        $this->assertResponseContains($expectedResult);
        $this->assertResponseOk();
    }
    
    public function testApiV1WorkshopsWithEmptySearchTermsToken(): void
    {
        $this->configRequest([
            'headers' => [
                'Authorization' => 'Bearer ' . ApiTokensFixture::EMPTY_SEARCH_TERMS_TOKEN,
                'Origin' => 'http://localhost',
            ],
        ]);
        $this->get('/api/v1/workshops?city=berlin');
        $this->assertResponseCode(401);
        $response = $this->getJsonResponseBody();
        $this->assertEquals('Access to this city is not allowed with this API token. Allowed search terms: none', $response['error']);
    }

    public function testApiV1WorkshopsCorsHeadersOnError(): void
    {
        $this->configRequest([
            'headers' => [
                'Authorization' => 'Bearer invalid-token',
                'Origin' => 'http://localhost',
            ],
        ]);
        $this->get('/api/v1/workshops?city=berlin');
        $this->assertResponseCode(401);
        $response = $this->getJsonResponseBody();
        $this->assertEquals('Invalid or inactive API token', $response['error']);
        $this->assertHeader('Access-Control-Allow-Origin', '*');
        $this->assertHeader('Access-Control-Allow-Methods', 'GET');
        $this->assertHeader('Access-Control-Allow-Headers', 'Authorization, Content-Type');
        $this->assertContentType('application/json');
    }

    public function testApiV1WorkshopsCorsHeadersOnSuccess(): void
    {
        $this->configRequest([
            'headers' => [
                'Authorization' => 'Bearer ' . ApiTokensFixture::VALID_TOKEN,
                'Origin' => 'http://localhost',
            ],
        ]);
        $this->get('/api/v1/workshops?city=berlin');
        $this->assertResponseOk();
        $this->assertHeader('Access-Control-Allow-Origin', '*');
        $this->assertHeader('Access-Control-Allow-Methods', 'GET');
        $this->assertHeader('Access-Control-Allow-Headers', 'Authorization, Content-Type');
    }

    public function testApiV1WorkshopsWithWrongDomain(): void
    {
        $this->configRequest([
            'headers' => [
                'Authorization' => 'Bearer ' . ApiTokensFixture::WRONG_DOMAIN_TOKEN,
                'Origin' => 'http://localhost',
            ],
        ]);
        $this->get('/api/v1/workshops?city=berlin');
        $this->assertResponseCode(401);
        $response = $this->getJsonResponseBody();
        $this->assertEquals('Invalid or inactive API token', $response['error']);
    }

}
