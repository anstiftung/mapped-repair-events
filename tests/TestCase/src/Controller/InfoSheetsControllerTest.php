<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Test\Fixture\BrandsFixture;
use App\Test\TestCase\AppTestCase;
use App\Test\TestCase\Traits\LogFileAssertionsTrait;
use App\Test\TestCase\Traits\LoginTrait;
use App\Test\TestCase\Traits\UserAssertionsTrait;
use Cake\Core\Configure;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\StringCompareTrait;

class InfoSheetsControllerTest extends AppTestCase
{
    use LoginTrait;
    use IntegrationTestTrait;
    use UserAssertionsTrait;
    use StringCompareTrait;
    use LogFileAssertionsTrait;

        /**
     * @var array<string, mixed>
     */
    private array $newInfoSheetData = [
        'category_id' => '',
        'new_subcategory_parent_id' => '',
        'new_subcategory_name' => '',
        'brand_id' => 1,
        'new_brand_name' => '',
        'device_name' => '',
        'device_age' => 1.5,
        'form_field_options' => [
            '_ids' => [
                '0' => 1
            ]
        ],
        'defect_description' => '',
        'defect_found_reason' => 2,
        'repair_postponed_reason' => 3,
        'no_repair_reason' => '',
        'device_must_not_be_used_anymore' => '',
        'no_repair_reason_text' => ''
    ];

    public function testAddInfoSheetValidations(): void
    {
        $this->loginAsOrga();
        $this->post(
            Configure::read('AppConfig.htmlHelper')->urlInfoSheetNew(6),
            [
                'InfoSheets' => $this->newInfoSheetData
            ]
        );
        $this->assertResponseContains('Bitte gib die Fehlerbeschreibung an (maximal 1.000 Zeichen).');
        $this->assertResponseContains('Bitte wähle eine Kategorie aus.');
    }

    public function testAddInfoSheetOk(): void
    {
        $this->loginAsOrga();
        $this->newInfoSheetData['defect_description'] = 'Defect description';
        $this->newInfoSheetData['device_name'] = 'Device name';
        $this->newInfoSheetData['category_id'] = 87;
        $this->newInfoSheetData['device_age'] = 1;
        $this->post(
            Configure::read('AppConfig.htmlHelper')->urlInfoSheetNew(6),
            [
                'referer' => '/',
                'InfoSheets' => $this->newInfoSheetData
            ]
        );
        $this->assertResponseNotContains('error');

        $infoSheetsTable = $this->getTableLocator()->get('InfoSheets');
        $infoSheets = $infoSheetsTable->find('all',
        contain: [
            'Events.Workshops',
            'Categories',
            'FormFieldOptions'
        ],
        order: [
            'InfoSheets.uid' => 'DESC',
        ])->toArray();
        $this->assertEquals(2, count($infoSheets));
        $this->assertEquals($infoSheets[0]->device_name, $this->newInfoSheetData['device_name']);
        $this->assertEquals($infoSheets[0]->defect_description, $this->newInfoSheetData['defect_description']);
        $this->assertEquals($infoSheets[0]->owner, 1);

        $expectedUid = $this->getTableLocator()->get('Roots')->find('all')->count();
        $this->assertFlashMessage('Laufzettel erfolgreich gespeichert. UID: ' . $expectedUid);
    }

    public function testAddInfoSheetWithDuplicateBrandUsesExistingBrand(): void
    {
        $this->loginAsOrga();
        $this->newInfoSheetData['defect_description'] = 'Defect description';
        $this->newInfoSheetData['device_name'] = 'Device name';
        $this->newInfoSheetData['category_id'] = 87;
        $this->newInfoSheetData['brand_id'] = -1;
        $this->newInfoSheetData['new_brand_name'] = 'abacom';

        $this->post(
            Configure::read('AppConfig.htmlHelper')->urlInfoSheetNew(6),
            [
                'referer' => '/',
                'InfoSheets' => $this->newInfoSheetData,
            ],
        );

        $this->assertEquals(2, $this->getTableLocator()->get('InfoSheets')->find()->count());
        $this->assertEquals(1, $this->getTableLocator()->get('Brands')->find()->count());
        $infoSheet = $this->getTableLocator()->get('InfoSheets')->find()->orderByDesc('uid')->first();
        $this->assertEquals(BrandsFixture::BRAND_ABACOM_ID, $infoSheet->brand_id);
    }

    public function testAddInfoSheetWithDuplicateSubcategoryUsesExistingCategory(): void
    {
        $this->loginAsOrga();
        $this->newInfoSheetData['defect_description'] = 'Defect description';
        $this->newInfoSheetData['device_name'] = 'Device name';
        $this->newInfoSheetData['category_id'] = -1;
        $this->newInfoSheetData['new_subcategory_parent_id'] = 1;
        $this->newInfoSheetData['new_subcategory_name'] = 'elektro sonstiges';

        $this->post(
            Configure::read('AppConfig.htmlHelper')->urlInfoSheetNew(6),
            [
                'referer' => '/',
                'InfoSheets' => $this->newInfoSheetData,
            ],
        );

        $this->assertEquals(2, $this->getTableLocator()->get('InfoSheets')->find()->count());
        $this->assertEquals(3, $this->getTableLocator()->get('Categories')->find()->count());
        $infoSheet = $this->getTableLocator()->get('InfoSheets')->find()->orderByDesc('uid')->first();
        $this->assertEquals(87, $infoSheet->category_id);
    }

    public function testAddInfoSheetWithSameSubcategoryNameInDifferentParentCategory(): void
    {
        $this->loginAsOrga();
        $this->newInfoSheetData['defect_description'] = 'Defect description';
        $this->newInfoSheetData['device_name'] = 'Device name';
        $this->newInfoSheetData['category_id'] = -1;
        $this->newInfoSheetData['new_subcategory_parent_id'] = 630;
        $this->newInfoSheetData['new_subcategory_name'] = 'elektro sonstiges';

        $this->post(
            Configure::read('AppConfig.htmlHelper')->urlInfoSheetNew(6),
            [
                'referer' => '/',
                'InfoSheets' => $this->newInfoSheetData,
            ],
        );

        $this->assertEquals(2, $this->getTableLocator()->get('InfoSheets')->find()->count());
        $this->assertEquals(4, $this->getTableLocator()->get('Categories')->find()->count());
        $infoSheet = $this->getTableLocator()->get('InfoSheets')->find()->orderByDesc('uid')->first();
        $this->assertNotEquals(87, $infoSheet->category_id);
    }

    public function testDeleteInfoSheetAsOrga(): void
    {
        $this->loginAsOrga();
        $this->get(Configure::read('AppConfig.htmlHelper')->urlInfoSheetDelete(7));
        $this->assertFlashMessage('Der Laufzettel wurde erfolgreich gelöscht.');

        $infoSheetsTable = $this->getTableLocator()->get('InfoSheets');
        $infoSheet = $infoSheetsTable->find('all', conditions: [
            'InfoSheets.uid' => 7
        ])->first();
        $this->assertEquals($infoSheet->status, APP_DELETED);
    }

    public function testDownloadAsOrga(): void
    {
        $this->loginAsOrga();
        $this->get('/laufzettel/download/2');
        $this->assertResponseCode(200);
        $this->_compareBasePath = ROOT . DS . 'tests' . DS . 'comparisons' . DS;
        $this->assertSameAsFile('info-sheets-download.csv', $this->_response->getBody()->__toString());
    }

}
?>