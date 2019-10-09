<?php

namespace App\Test\TestCase\Controller;

use App\Test\TestCase\Traits\UserAssertionsTrait;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\StringCompareTrait;
use Cake\TestSuite\TestCase;

class WorkshopsControllerTest extends TestCase
{
    use IntegrationTestTrait;
    use UserAssertionsTrait;
    use StringCompareTrait;
    
    public $fixtures = [
        'app.Categories',
        'app.Countries',
        'app.Events',
        'app.Groups',
        'app.InfoSheets',
        'app.Metatags',
        'app.Pages',
        'app.Users',
        'app.UsersGroups',
        'app.UsersWorkshops',
        'app.Worknews',
        'app.Workshops',
        'app.WorkshopsCategories'
    ];

    public function testAjaxGetAllWorkshopsForMap()
    {
        $this->configRequest([
            'headers' => [
                'X_REQUESTED_WITH' => 'XMLHttpRequest'
            ]
        ]);
        $this->_compareBasePath = ROOT . DS . 'tests' . DS . 'comparisons' . DS;
        $this->get('/workshops/ajaxGetAllWorkshopsForMap');
        $this->assertSameAsFile('workshops-for-map.json', $this->_response);
    }
    
    public function testWorkshopDetail()
    {
        $this->get(Configure::read('AppConfig.htmlHelper')->urlWorkshopDetail('test-workshop'));
        $this->assertResponseOk();
        $this->assertResponseNotEmpty();
        $this->doUserPrivacyAssertions();
    }
    
    
}
?>