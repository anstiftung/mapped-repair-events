<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Test\Fixture\ApiTokensFixture;
use App\Test\TestCase\AppTestCase;
use Cake\Core\Configure;
use Cake\I18n\Date;

class ApiControllerTest extends AppTestCase {

    public function testWorkshopsBerlin(): void
    {
        $this->configRequest([
            'headers' => [
                'Authorization' => 'Bearer ' . ApiTokensFixture::VALID_WORKSHOPS_TOKEN,
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

    public function testWorkshopsHamburg(): void
    {
        $this->configRequest([
            'headers' => [
                'Authorization' => 'Bearer ' . ApiTokensFixture::VALID_WORKSHOPS_TOKEN,
                'Origin' => 'http://localhost',
            ],
        ]);
        $this->get('/api/v1/workshops?city=hamburg');
        $this->assertResponseCode(401);
        $response = $this->getJsonResponseBody();
        $this->assertEquals('Access to this city is not allowed with this API token. Allowed search terms: Berlin, München', $response['error']);
    }

    public function testWorkshopsWrongParam(): void
    {
        $this->configRequest([
            'headers' => [
                'Authorization' => 'Bearer ' . ApiTokensFixture::VALID_WORKSHOPS_TOKEN,
                'Origin' => 'http://localhost',
            ],
        ]);
        $this->get('/api/v1/workshops?city=ha');
        $this->assertResponseCode(401);
        $response = $this->getJsonResponseBody();
        $this->assertEquals('Access to this city is not allowed with this API token. Allowed search terms: Berlin, München', $response['error']);
    }
    public function testWorkshopsWithoutToken(): void
    {
        $this->get('/api/v1/workshops?city=berlin');
        $this->assertResponseCode(401);
        $response = $this->getJsonResponseBody();
        $this->assertEquals('API token is required. Please provide a valid token in the Authorization header as Bearer token.', $response['error']);
    }

    public function testWorkshopsWithInvalidToken(): void
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

    public function testWorkshopsWithInactiveToken(): void
    {
        $this->configRequest([
            'headers' => [
                'Authorization' => 'Bearer ' . ApiTokensFixture::INACTIVE_WORKSHOPS_TOKEN,
                'Origin' => 'http://localhost',
            ],
        ]);
        $this->get('/api/v1/workshops?city=berlin');
        $this->assertResponseCode(401);
        $response = $this->getJsonResponseBody();
        $this->assertEquals('Invalid or inactive API token', $response['error']);
    }

    public function testWorkshopsWithTokenRequestingNonValidCity(): void
    {
        $this->configRequest([
            'headers' => [
                'Authorization' => 'Bearer ' . ApiTokensFixture::VALID_WORKSHOPS_TOKEN,
                'Origin' => 'http://localhost',
            ],
        ]);
        $this->get('/api/v1/workshops?city=köln');
        $this->assertResponseCode(401);
        $response = $this->getJsonResponseBody();
        $this->assertEquals('Access to this city is not allowed with this API token. Allowed search terms: Berlin, München', $response['error']);
    }

    public function testWorkshopsWithBearerToken(): void
    {
        $this->configRequest([
            'headers' => [
                'Authorization' => 'Bearer ' . ApiTokensFixture::VALID_WORKSHOPS_TOKEN,
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
    
    public function testWorkshopsWithEmptySearchTermsToken(): void
    {
        $this->configRequest([
            'headers' => [
                'Authorization' => 'Bearer ' . ApiTokensFixture::EMPTY_WORKSHOPS_SEARCH_TERMS_TOKEN,
                'Origin' => 'http://localhost',
            ],
        ]);
        $this->get('/api/v1/workshops?city=berlin');
        $this->assertResponseCode(401);
        $response = $this->getJsonResponseBody();
        $this->assertEquals('Access to this city is not allowed with this API token. Allowed search terms: none', $response['error']);
    }

    public function testWorkshopsCorsHeadersOnError(): void
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

    public function testWorkshopsCorsHeadersOnSuccess(): void
    {
        $this->configRequest([
            'headers' => [
                'Authorization' => 'Bearer ' . ApiTokensFixture::VALID_WORKSHOPS_TOKEN,
                'Origin' => 'http://localhost',
            ],
        ]);
        $this->get('/api/v1/workshops?city=berlin');
        $this->assertResponseOk();
        $this->assertHeader('Access-Control-Allow-Origin', '*');
        $this->assertHeader('Access-Control-Allow-Methods', 'GET');
        $this->assertHeader('Access-Control-Allow-Headers', 'Authorization, Content-Type');
    }

    public function testWorkshopsWithWrongDomain(): void
    {
        $this->configRequest([
            'headers' => [
                'Authorization' => 'Bearer ' . ApiTokensFixture::WRONG_DOMAIN_WORKSHOPS_TOKEN,
                'Origin' => 'http://localhost',
            ],
        ]);
        $this->get('/api/v1/workshops?city=berlin');
        $this->assertResponseCode(401);
        $response = $this->getJsonResponseBody();
        $this->assertEquals('Invalid or inactive API token', $response['error']);
    }

    public function testGetSplitterOk(): void
    {
        Configure::write('AppConfig.splitterPath', '/files'); // prevent errors on github actions
        $this->configRequest([
            'headers' => [
                'Authorization' => 'Bearer ' . ApiTokensFixture::VALID_SPLITTER_TOKEN,
                'Origin' => 'http://localhost',
            ],
        ]);
        $this->get('/api/splitter');
        $this->assertResponseOk();
    }

    public function testGetWorkshopsForHyperModeWebsite(): void
    {
        $this->configRequest([
            'headers' => [
                'Authorization' => 'Bearer ' . ApiTokensFixture::VALID_WORKSHOPS_HYPERMODE_TOKEN,
                'Origin' => 'http://localhost',
            ],
        ]);
        $this->get('/api/workshops');
        $this->assertResponseOk();
        $response = $this->getJsonResponseBody();
        $this->assertArrayHasKey('workshops', $response);
        $this->assertIsArray($response['workshops']);
        $this->assertCount(1, $response['workshops']);
    }

}
