<?php
declare(strict_types=1);
namespace App\Test\TestCase\Model\Table;

use App\Model\Table\BrandsTable;
use App\Test\Fixture\BrandsFixture;
use App\Test\Fixture\CategoriesFixture;
use App\Test\TestCase\AppTestCase;
use Cake\ORM\TableRegistry;

class BrandsTableTest extends AppTestCase
{
    protected BrandsTable $Brands;

    public function setUp(): void
    {
        parent::setUp();
        $this->Brands = $this->getTableLocator()->get('Brands');
    }

    public function testGetInfoSheetCounts(): void
    {
        $infoSheetsTable = TableRegistry::getTableLocator()->get('InfoSheets');
        $infoSheetsTable->saveManyOrFail($infoSheetsTable->newEntities([
            [
                'status' => APP_ON,
                'event_uid' => 6,
                'category_id' => CategoriesFixture::SUB_CATEGORY_ID_ELECTRO_SONSTIGES,
                'brand_id' => BrandsFixture::BRAND_ABACOM_ID,
            ],
            [
                'status' => APP_ON,
                'event_uid' => 6,
                'category_id' => CategoriesFixture::SUB_CATEGORY_ID_ELECTRO_KLEINGERAETE,
                'brand_id' => BrandsFixture::BRAND_ABACOM_ID,
            ],
        ], ['validate' => false]));

        $counts = $this->Brands->getInfoSheetCounts([
            BrandsFixture::BRAND_ABACOM_ID,
        ]);

        $this->assertEquals(2, $counts[BrandsFixture::BRAND_ABACOM_ID]);
    }

    public function testGetInfoSheetCountsWithNoMatches(): void
    {
        $counts = $this->Brands->getInfoSheetCounts([-1]);

        $this->assertSame([], $counts);
    }
}
?>
