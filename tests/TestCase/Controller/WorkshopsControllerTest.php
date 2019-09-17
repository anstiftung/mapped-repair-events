<?php

namespace App\Test\TestCase\Controller;

use App\Test\Fixture\UsersFixture;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\StringCompareTrait;
use Cake\TestSuite\TestCase;

class WorkshopsControllerTest extends TestCase
{
    use IntegrationTestTrait;
    use StringCompareTrait;

    public $fixtures = [
        'app.Categories',
        'app.Countries',
        'app.Events',
        'app.Groups',
        'app.Pages',
        'app.Users',
        'app.UsersGroups',
        'app.UsersWorkshops',
        'app.Workshops'
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
    
}
?>