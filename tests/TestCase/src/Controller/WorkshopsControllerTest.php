<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Test\TestCase\AppTestCase;
use App\Test\TestCase\Traits\LogFileAssertionsTrait;
use App\Test\TestCase\Traits\LoginTrait;
use App\Test\TestCase\Traits\UserAssertionsTrait;
use Cake\Core\Configure;
use Cake\TestSuite\EmailTrait;
use Cake\TestSuite\StringCompareTrait;
use App\Services\GeoService;
use App\Test\TestCase\Traits\QueueTrait;
use Cake\I18n\Date;
use Cake\Event\EventInterface;
use Cake\Controller\Controller;
use App\Test\Mock\GeoServiceMock;

class WorkshopsControllerTest extends AppTestCase
{
    use LoginTrait;
    use UserAssertionsTrait;
    use StringCompareTrait;
    use EmailTrait;
    use LogFileAssertionsTrait;
    use QueueTrait;

	public function controllerSpy(EventInterface $event, ?Controller $controller = null): void
    {
		parent::controllerSpy($event, $controller);
		$this->_controller->geoService = new GeoServiceMock();
	}

    public function testAjaxGetAllWorkshopsWithFundingsForMap(): void
    {
        $this->configRequest([
            'headers' => [
                'X_REQUESTED_WITH' => 'XMLHttpRequest',
            ]
        ]);
        $this->get('/workshops/ajaxGetAllWorkshopsForMap?keyword=workshops-with-fundings');
        $this->assertResponseOk();
    }

    public function testAjaxGetAllWorkshopsForMap(): void
    {

        $expectedResult = file_get_contents(TESTS . 'comparisons' . DS . 'rest-workshops-berlin.json');
        $expectedResult = $this->correctServerName($expectedResult);
        $expectedNextEventDate = Date::now()->addDays(7)->format('d.m.Y');
        $expectedResult = $this->correctExpectedDate($expectedResult, $expectedNextEventDate);
        $this->get('/api/v1/workshops?city=berlin');
        $this->assertResponseContains($expectedResult);
        $this->assertResponseOk();

        $this->configRequest([
            'headers' => [
                'X_REQUESTED_WITH' => 'XMLHttpRequest',
            ]
        ]);
        $expectedResult = file_get_contents(TESTS . 'comparisons' . DS . 'workshops-for-map.json');
        $expectedNextEventDate = Date::now()->addDays(7)->format('Y-m-d');
        $expectedResult = $this->correctExpectedDate($expectedResult, $expectedNextEventDate);
        $this->get('/workshops/ajaxGetAllWorkshopsForMap');
        $this->assertResponseContains($expectedResult);
        $this->assertResponseOk();
    }

    public function testWorkshopDetail(): void
    {
        $this->get(Configure::read('AppConfig.htmlHelper')->urlWorkshopDetail('test-workshop'));
        $this->assertResponseOk();
        $this->assertResponseNotEmpty();
        $this->doUserPrivacyAssertions();
    }

    public function testWorkshopSearchWithExceptionKeyword(): void
    {
        $this->get(Configure::read('AppConfig.htmlHelper')->urlWorkshops('aachen'));
        $this->assertResponseOk();
        $this->assertResponseNotEmpty();
        $this->assertResponseContains('<div class="numbers">0 Initiativen gefunden</div>');
    }

    public function testWorkshopSearchWithNonExceptionKeyword(): void
    {
        $this->get(Configure::read('AppConfig.htmlHelper')->urlWorkshops('Test'));
        $this->assertResponseOk();
        $this->assertResponseNotEmpty();
        $this->assertResponseContains('<div class="numbers">1 Initiative gefunden</div>');
    }

    public function testApplyToWorkshopAsRepairhelper(): void
    {

        $this->executeLogFileAssertions = false;

        $workshopUid = 2;
        $userUid = 3;
        $this->loginAsRepairhelper();

        $this->post(
            Configure::read('AppConfig.htmlHelper')->urlUserWorkshopApplicationUser(),
            [
                'referer' => '/',
                'users_workshops' => [
                    'workshop_uid' => $workshopUid,
                    'user_uid' => $userUid,
                ]
            ]
        );
        $this->runAndAssertQueue();

        $workshopsTable = $this->getTableLocator()->get('Workshops');
        $workshop = $workshopsTable->find('all',
        conditions: [
            'Workshops.uid' => $workshopUid,
        ],
        contain: [
            'Users',
        ])->first();

        $this->assertEquals(2, count($workshop->users));
        $this->assertEquals($userUid, $workshop->users[1]->uid);
        $this->assertEquals(null, $workshop->users[1]->_joinData->approved);

        $this->assertMailCount(1);
        $this->assertMailContainsAt(0, 'Max Muster (maxmuster@mailinator.com) möchte bei ');
        $this->assertMailSentToAt(0, 'johndoe@mailinator.com');

        $this->get(Configure::read('AppConfig.htmlHelper')->urlWorkshopEdit($workshopUid));
        $this->assertResponseCode(302);
        $this->assertRedirectContains('/users/login?redirect=%2Finitiativen%2Fbearbeiten%2F2');

    }

    public function testAddWorkshopWithCustomCoordinates(): void
    {

        $workshopForPost = [
            'name' => 'test initiative',
            'url' => 'test-initiative',
            'use_custom_coordinates' => true,
            'lat' => 52.520008,
            'lng' => 13.404954,
            'province_id' => 1,
        ];

        $this->loginAsOrga();
        $this->post(
            Configure::read('AppConfig.htmlHelper')->urlWorkshopNew(),
            [
                'referer' => '/',
                'Workshops' => $workshopForPost,
            ]
        );
        $this->runAndAssertQueue();

        $workshopsTable = $this->getTableLocator()->get('Workshops');
        $workshop = $workshopsTable->find('all', conditions: [
            'Workshops.url' => $workshopForPost['url']
        ])->first();

        $this->assertEquals($workshop->name, $workshopForPost['name']);
        $this->assertEquals($workshop->url, $workshopForPost['url']);
        $this->assertEquals($workshop->province_id, $workshopForPost['province_id']);

        $this->assertMailCount(1);
        $this->assertMailSentTo(Configure::read('AppConfig.debugMailAddress'));
        $this->assertMailContainsHtmlAt(0, '"test initiative" erstellt');

    }

    public function testAddWorkshopWithWrongGeoData(): void
    {

        $workshopForPost = [
            'name' => 'test initiative',
            'url' => 'test-initiative',
            'use_custom_coordinates' => true,
            'lat' => 13.404954, // wrong - data swapped: lat = lng
            'lng' => 52.520008, // wrong - data swapped: lng = lat
        ];

        $this->loginAsOrga();
        $this->post(
            Configure::read('AppConfig.htmlHelper')->urlWorkshopNew(),
            [
                'referer' => '/',
                'Workshops' => $workshopForPost
            ]
        );

        $this->assertResponseContains(GeoService::ERROR_OUT_OF_BOUNDING_BOX);
        $this->assertMailCount(0);

    }


    public function testEditWorkshopAsOrga(): void
    {
        $this->loginAsOrga();
        $workshopUid = 2;
        $this->post(
            Configure::read('AppConfig.htmlHelper')->urlWorkshopEdit($workshopUid),
            [
                'referer' => '/',
                'Workshops' => [
                    'name' => 'Test Workshop',
                    'url' => 'test-workshop',
                    'use_custom_coordinates' => true,
                    'text' => '<iframe></iframe>workshop info',
                    'lat' => 52.520008,
                    'lng' => 13.404954,
                    'province_id' => 1,
                ]
            ]
        );
        $this->runAndAssertQueue();

        $this->assertMailCount(1);
        $this->assertMailSentTo(Configure::read('AppConfig.debugMailAddress'));
        $this->assertMailContainsHtmlAt(0, '"Test Workshop" geändert');

        $workshopsTable = $this->getTableLocator()->get('Workshops');
        $workshop = $workshopsTable->find('all', conditions: [
            'Workshops.uid' => $workshopUid,
        ])->first();
        $this->assertEquals('workshop info', $workshop->text);

    }

    public function testAjaxGetWorkshopsAndUsersForTags(): void
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


    public function testRestWorkshopsBerlin(): void
    {
        $expectedResult = file_get_contents(TESTS . 'comparisons' . DS . 'rest-workshops-berlin.json');
        $expectedResult = $this->correctServerName($expectedResult);
        $expectedNextEventDate = Date::now()->addDays(7)->format('d.m.Y');
        $expectedResult = $this->correctExpectedDate($expectedResult, $expectedNextEventDate);
        $this->get('/api/v1/workshops?city=berlin');
        $this->assertResponseContains($expectedResult);
        $this->assertResponseOk();
    }

    public function testRestWorkshopsHamburg(): void
    {
        $this->get('/api/v1/workshops?city=hamburg');
        $this->assertResponseContains('no workshops found');
        $this->assertResponseCode(404);
    }

    public function testRestWorkshopsWrongParam(): void
    {
        $this->get('/api/v1/workshops?city=ha');
        $this->assertResponseContains('city not passed or invalid (min 3 chars)');
        $this->assertResponseCode(400);
    }

}
?>