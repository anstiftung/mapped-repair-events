<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

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