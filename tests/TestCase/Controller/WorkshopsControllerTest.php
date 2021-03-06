<?php

namespace App\Test\TestCase\Controller;

use App\Test\TestCase\AppTestCase;
use App\Test\TestCase\Traits\LogFileAssertionsTrait;
use App\Test\TestCase\Traits\LoginTrait;
use App\Test\TestCase\Traits\UserAssertionsTrait;
use Cake\Core\Configure;
use Cake\TestSuite\EmailTrait;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\StringCompareTrait;

class WorkshopsControllerTest extends AppTestCase
{
    use LoginTrait;
    use IntegrationTestTrait;
    use UserAssertionsTrait;
    use StringCompareTrait;
    use EmailTrait;
    use LogFileAssertionsTrait;

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

    public function testAddWorkshop()
    {

        $workshopForPost = [
            'name' => 'test initiative',
            'url' => 'test-initiative',
            'use_custom_coordinates' => true,
            'lat' => 0,
            'lng' => 0,
        ];

        $this->loginAsOrga();
        $this->post(
            Configure::read('AppConfig.htmlHelper')->urlWorkshopNew(),
            [
                'referer' => '/',
                'Workshops' => $workshopForPost
            ]
        );

        $this->Workshop = $this->getTableLocator()->get('Workshops');
        $workshop = $this->Workshop->find('all', [
            'conditions' => [
                'Workshops.url' => $workshopForPost['url']
            ]
        ])->first();

        $this->assertEquals($workshop->name, $workshopForPost['name']);
        $this->assertEquals($workshop->url, $workshopForPost['url']);

        $this->assertMailCount(1);
        $this->assertMailSentTo(Configure::read('AppConfig.debugMailAddress'));
        $this->assertMailContainsTextAt(0, '"test initiative" erstellt');

    }

    public function testEditWorkshopNotifications()
    {
        $this->loginAsOrga();
        $this->post(
            Configure::read('AppConfig.htmlHelper')->urlWorkshopEdit(2),
            [
                'referer' => '/',
                'Workshops' => [
                    'name' => 'Test Workshop',
                    'url' => 'test-workshop',
                    'use_custom_coordinates' => true,
                    'lat' => 0,
                    'lng' => 0,
                ]
            ]
        );

        $this->assertMailCount(1);
        $this->assertMailSentTo(Configure::read('AppConfig.debugMailAddress'));
        $this->assertMailContainsTextAt(0, '"Test Workshop" geändert');

    }

    public function testAjaxGetWorkshopsAndUsersForTags()
    {
        $this->configRequest([
            'headers' => [
                'X_REQUESTED_WITH' => 'XMLHttpRequest'
            ],
        ]);
        $expectedResult = file_get_contents(TESTS . 'comparisons' . DS . 'data-for-vow-tags-widget.json');
        $expectedResult = $this->correctServerName($expectedResult);
        $this->get('/workshops/ajaxGetWorkshopsAndUsersForTags?tags[]=3dreparieren');
        $this->assertResponseContains($expectedResult);
    }

}
?>