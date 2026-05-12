<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Model\Entity\ApiToken;
use App\Test\Fixture\ApiTokensFixture;
use App\Test\TestCase\AppTestCase;
use Cake\Cache\Cache;

class ApiControllerStatisticsTest extends AppTestCase
{
    public function testGetStatistics(): void
    {
        $this->getTableLocator()->get('Events')->updateAll([
            'ort' => 'Berlin',
            'province_id' => 1,
            'datumstart' => '2040-01-01',
        ], [
            'uid' => 6,
        ]);
        $this->prepareStatisticsInfoSheets();

        $this->configRequest([
            'headers' => [
                'Authorization' => 'Bearer ' . ApiTokensFixture::VALID_STATISTICS_TOKEN,
                'Origin' => 'http://localhost',
            ],
        ]);
        $this->get('/api/v1/statistics?city=Berlin&dateFrom=2039-01-01&dateTo=2040-12-31');
        $this->assertResponseOk();
        $response = $this->getJsonResponseBody();
        $expectedResponse = json_decode((string)file_get_contents(TESTS . 'comparisons' . DS . 'rest-statistics-berlin.json'), true);

        $this->assertSame($expectedResponse, $response);
        $this->assertStatisticsCategoriesAreSortedAlphabetically($response['statistics']['categories']);
    }

    public function testGetStatisticsForBayern(): void
    {
        $this->prepareStatisticsInfoSheets();

        $this->configRequest([
            'headers' => [
                'Authorization' => 'Bearer ' . ApiTokensFixture::VALID_STATISTICS_TOKEN,
                'Origin' => 'http://localhost',
            ],
        ]);
        $this->get('/api/v1/statistics?province=Bayern&dateFrom=2039-01-01&dateTo=2040-12-31');
        $this->assertResponseOk();
        $response = $this->getJsonResponseBody();
        $expectedResponse = json_decode((string)file_get_contents(TESTS . 'comparisons' . DS . 'rest-statistics-bayern.json'), true);

        $this->assertSame($expectedResponse, $response);
        $this->assertStatisticsCategoriesAreSortedAlphabetically($response['statistics']['categories']);
    }

    public function testGetStatisticsRequiresStatisticsToken(): void
    {
        $this->configRequest([
            'headers' => [
                'Authorization' => 'Bearer ' . ApiTokensFixture::VALID_SPLITTER_TOKEN,
                'Origin' => 'http://localhost',
            ],
        ]);
        $this->get('/api/v1/statistics');
        $this->assertResponseCode(401);
        $response = $this->getJsonResponseBody();
        $this->assertEquals('Invalid or inactive API token', $response['error']);
    }

    public function testGetStatisticsRequiresCityOrProvince(): void
    {
        $this->configRequest([
            'headers' => [
                'Authorization' => 'Bearer ' . ApiTokensFixture::VALID_STATISTICS_TOKEN,
                'Origin' => 'http://localhost',
            ],
        ]);
        $this->get('/api/v1/statistics');
        $this->assertResponseCode(400);
        $response = $this->getJsonResponseBody();
        $this->assertEquals('city or province must be provided', $response['error']);
    }

    public function testGetStatisticsDeniesDisallowedCity(): void
    {
        $this->configRequest([
            'headers' => [
                'Authorization' => 'Bearer ' . ApiTokensFixture::VALID_STATISTICS_TOKEN,
                'Origin' => 'http://localhost',
            ],
        ]);
        $this->get('/api/v1/statistics?city=Hamburg');
        $this->assertResponseCode(401);
        $response = $this->getJsonResponseBody();
        $this->assertEquals('Access to this city is not allowed with this API token. Allowed search terms: Berlin, Bayern, Niedersachsen', $response['error']);
    }

    public function testGetStatisticsDeniesDisallowedProvince(): void
    {
        $this->configRequest([
            'headers' => [
                'Authorization' => 'Bearer ' . ApiTokensFixture::VALID_STATISTICS_TOKEN,
                'Origin' => 'http://localhost',
            ],
        ]);
        $this->get('/api/v1/statistics?province=Hamburg');
        $this->assertResponseCode(401);
        $response = $this->getJsonResponseBody();
        $this->assertEquals('Access to this province is not allowed with this API token. Allowed search terms: Berlin, Bayern, Niedersachsen', $response['error']);
    }

    public function testGetStatisticsRejectsBothCityAndProvince(): void
    {
        $this->configRequest([
            'headers' => [
                'Authorization' => 'Bearer ' . ApiTokensFixture::VALID_STATISTICS_TOKEN,
                'Origin' => 'http://localhost',
            ],
        ]);
        $this->get('/api/v1/statistics?city=Berlin&province=Bayern');
        $this->assertResponseCode(400);
        $response = $this->getJsonResponseBody();
        $this->assertEquals('Cannot specify both city and province parameters. Please specify only one.', $response['error']);
    }

    public function testGetStatisticsRejectsEmptyCity(): void
    {
        $this->configRequest([
            'headers' => [
                'Authorization' => 'Bearer ' . ApiTokensFixture::VALID_STATISTICS_TOKEN,
                'Origin' => 'http://localhost',
            ],
        ]);
        $this->get('/api/v1/statistics?city=');
        $this->assertResponseCode(400);
        $response = $this->getJsonResponseBody();
        $this->assertEquals('city or province must be provided', $response['error']);
    }

    public function testGetStatisticsRejectsEmptyProvince(): void
    {
        $this->configRequest([
            'headers' => [
                'Authorization' => 'Bearer ' . ApiTokensFixture::VALID_STATISTICS_TOKEN,
                'Origin' => 'http://localhost',
            ],
        ]);
        $this->get('/api/v1/statistics?province=');
        $this->assertResponseCode(400);
        $response = $this->getJsonResponseBody();
        $this->assertEquals('city or province must be provided', $response['error']);
    }

    public function testGetStatisticsWithInvalidProvince(): void
    {
        $this->configRequest([
            'headers' => [
                'Authorization' => 'Bearer ' . ApiTokensFixture::VALID_STATISTICS_TOKEN,
                'Origin' => 'http://localhost',
            ],
        ]);
        $this->get('/api/v1/statistics?province=Niedersachsen');
        $this->assertResponseCode(404);
        $response = $this->getJsonResponseBody();
        $this->assertEquals('province not found', $response['error']);
    }

    public function testGetStatisticsRateLimitExceeded(): void
    {
        $cacheKey = 'api_rate_limit_' . ApiToken::TYPE_STATISTICS . '_7';
        $cacheWasEnabled = Cache::enabled();
        $reset = time() + 60;

        Cache::enable();
        Cache::delete($cacheKey);
        $this->assertTrue(Cache::write($cacheKey, [
            'count' => 60,
            'reset' => $reset,
        ]));

        try {
            $this->configRequest([
                'headers' => [
                    'Authorization' => 'Bearer ' . ApiTokensFixture::VALID_STATISTICS_TOKEN,
                    'Origin' => 'http://localhost',
                ],
            ]);
            $this->get('/api/v1/statistics?city=Berlin');
            $this->assertResponseCode(429);
            $response = $this->getJsonResponseBody();
            $retryAfter = (int)$this->_response->getHeaderLine('Retry-After');

            $this->assertEquals('rate limit exceeded', $response['error']);
            $this->assertSame('60', $this->_response->getHeaderLine('X-RateLimit-Limit'));
            $this->assertSame('0', $this->_response->getHeaderLine('X-RateLimit-Remaining'));
            $this->assertSame((string)$reset, $this->_response->getHeaderLine('X-RateLimit-Reset'));
            $this->assertGreaterThanOrEqual(1, $retryAfter);
            $this->assertLessThanOrEqual(60, $retryAfter);
        } finally {
            Cache::delete($cacheKey);
            if (!$cacheWasEnabled) {
                Cache::disable();
            }
        }
    }

    private function prepareStatisticsInfoSheets(): void
    {
        $this->getTableLocator()->get('InfoSheets')->updateAll([
            'defect_found_reason' => 1,
        ], [
            'event_uid' => 6,
        ]);
    }

    /**
     * @param list<array{label: string}> $categories
     */
    private function assertStatisticsCategoriesAreSortedAlphabetically(array $categories): void
    {
        $labels = array_column($categories, 'label');
        $sortedLabels = $labels;
        natcasesort($sortedLabels);
        $this->assertSame(array_values($sortedLabels), $labels);
    }
}
