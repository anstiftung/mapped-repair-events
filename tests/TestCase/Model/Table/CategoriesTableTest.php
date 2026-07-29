<?php
declare(strict_types=1);
namespace App\Test\TestCase\Model\Table;

use App\Model\Table\CategoriesTable;
use App\Test\Fixture\CategoriesFixture;
use App\Test\TestCase\AppTestCase;

class CategoriesTableTest extends AppTestCase
{
    protected CategoriesTable $Categories;

    public function setUp(): void
    {
        parent::setUp();
        $this->Categories = $this->getTableLocator()->get('Categories');
    }

    public function testGetInfoSheetCounts(): void
    {
        $counts = $this->Categories->getInfoSheetCounts([
            CategoriesFixture::SUB_CATEGORY_ID_ELECTRO_SONSTIGES,
            CategoriesFixture::SUB_CATEGORY_ID_ELECTRO_KLEINGERAETE,
        ]);

        $this->assertEquals(2, $counts[CategoriesFixture::SUB_CATEGORY_ID_ELECTRO_SONSTIGES]);
        $this->assertEquals(1, $counts[CategoriesFixture::SUB_CATEGORY_ID_ELECTRO_KLEINGERAETE]);
    }

    public function testGetInfoSheetCountsWithNoMatches(): void
    {
        $counts = $this->Categories->getInfoSheetCounts([-1]);

        $this->assertSame([], $counts);
    }
}
?>
