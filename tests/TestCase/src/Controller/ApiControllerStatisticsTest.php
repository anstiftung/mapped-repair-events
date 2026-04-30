<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Test\Fixture\ApiTokensFixture;
use App\Test\TestCase\AppTestCase;

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
        $this->get('/api/statistics?city=Berlin&dateFrom=2039-01-01&dateTo=2040-12-31');
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
        $this->get('/api/statistics?province=Bayern&dateFrom=2039-01-01&dateTo=2040-12-31');
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
        $this->get('/api/statistics');
        $this->assertResponseCode(401);
        $response = $this->getJsonResponseBody();
        $this->assertEquals('Invalid or inactive API token', $response['error']);
    }

    public function testGetStatisticsDeniesDisallowedCity(): void
    {
        $this->configRequest([
            'headers' => [
                'Authorization' => 'Bearer ' . ApiTokensFixture::VALID_STATISTICS_TOKEN,
                'Origin' => 'http://localhost',
            ],
        ]);
        $this->get('/api/statistics?city=Hamburg');
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
        $this->get('/api/statistics?province=Hamburg');
        $this->assertResponseCode(401);
        $response = $this->getJsonResponseBody();
        $this->assertEquals('Access to this province is not allowed with this API token. Allowed search terms: Berlin, Bayern, Niedersachsen', $response['error']);
    }

    public function testGetStatisticsWithInvalidProvince(): void
    {
        $this->configRequest([
            'headers' => [
                'Authorization' => 'Bearer ' . ApiTokensFixture::VALID_STATISTICS_TOKEN,
                'Origin' => 'http://localhost',
            ],
        ]);
        $this->get('/api/statistics?province=Niedersachsen');
        $this->assertResponseCode(404);
        $response = $this->getJsonResponseBody();
        $this->assertEquals('province not found', $response['error']);
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
