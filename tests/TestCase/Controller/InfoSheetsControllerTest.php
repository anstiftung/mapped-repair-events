<?php

namespace App\Test\TestCase\Controller;

use App\Test\TestCase\Traits\LoadAllFixturesTrait;
use App\Test\TestCase\Traits\LogFileAssertionsTrait;
use App\Test\TestCase\Traits\LoginTrait;
use App\Test\TestCase\Traits\UserAssertionsTrait;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\StringCompareTrait;
use Cake\TestSuite\TestCase;

class InfoSheetsControllerTest extends TestCase
{
    use LoginTrait;
    use IntegrationTestTrait;
    use UserAssertionsTrait;
    use StringCompareTrait;
    use LogFileAssertionsTrait;
    use LoadAllFixturesTrait;
    
    private $newInfoSheetData = [
        'visitor_gender' => 'm',
        'visitor_age' => '',
        'category_id' => '',
        'new_subcategory_parent_id' => '',
        'new_subcategory_name' => '',
        'brand_id' => 1,
        'new_brand_name' => '',
        'device_name' => '',
        'device_age' => 1,
        'form_field_options' => [
            '_ids' => [
                '0' => 1
            ]
        ],
        'defect_description' => '',
        'defect_found' => '',
        'defect_found_reason' => 2,
        'repair_postponed_reason' => 3,
        'no_repair_reason' => '',
        'device_must_not_be_used_anymore' => '',
        'no_repair_reason_text' => ''
    ];
    
    public function testAddInfoSheetValidations()
    {
        $this->loginAsOrga();
        $this->post(
            Configure::read('AppConfig.htmlHelper')->urlInfoSheetNew(6),
            [
                'InfoSheets' => $this->newInfoSheetData
            ]
        );
        $this->assertResponseContains('Bitte gib die Fehlerbeschreibung an (maximal 1.000 Zeichen).');
        $this->assertResponseContains('Bitte trage ein, um welches Gerät es sich handelt.');
        $this->assertResponseContains('Bitte wähle eine Kategorie aus.');
        $this->assertResponseContains('Wurde der Fehler gefunden?');
    }
    
    public function testAddInfoSheetOk()
    {
        $this->loginAsOrga();
        $this->newInfoSheetData['defect_description'] = 'Defect description';
        $this->newInfoSheetData['device_name'] = 'Device name';
        $this->newInfoSheetData['category_id'] = 87;
        $this->newInfoSheetData['defect_found'] = 1;
        $this->post(
            Configure::read('AppConfig.htmlHelper')->urlInfoSheetNew(6),
            [
                'referer' => '/',
                'InfoSheets' => $this->newInfoSheetData
            ]
        );
        $this->assertResponseNotContains('error');
        
        $this->InfoSheet = TableRegistry::getTableLocator()->get('InfoSheets');
        $infoSheets = $this->InfoSheet->find('all', [
            'contain' => [
                'Events.Workshops',
                'Categories',
                'FormFieldOptions'
            ]
        ])->toArray();
        $this->assertEquals(1, count($infoSheets));
        $this->assertEquals($infoSheets[0]->device_name, $this->newInfoSheetData['device_name']);
        $this->assertEquals($infoSheets[0]->defect_description, $this->newInfoSheetData['defect_description']);
    }
    
}
?>