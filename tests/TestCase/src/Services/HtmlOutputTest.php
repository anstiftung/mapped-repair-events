<?php
declare(strict_types=1);

namespace App\Test\TestCase\Services;

use Cake\Core\Configure;
use App\Test\TestCase\AppTestCase;
use App\Test\TestCase\Traits\LoginTrait;
use Cake\TestSuite\IntegrationTestTrait;
use App\Test\TestCase\Traits\LogFileAssertionsTrait;
use App\Test\TestCase\Traits\HtmlOutputAssertionsTrait;
use Cake\I18n\Date;

class HtmlOutputTest extends AppTestCase
{
    use IntegrationTestTrait;
    use HtmlOutputAssertionsTrait;
    use LogFileAssertionsTrait;
    use LoginTrait;

    private $User;

    public function testHome()
    {
        $this->get('/');
        $this->doAssertHtmlOutput();
    }

    public function testWorkshopDetail()
    {
        $this->get(Configure::read('AppConfig.htmlHelper')->urlWorkshopDetail('test-workshop'));
        $this->doAssertHtmlOutput();
    }

    public function testPostDetail()
    {
        $this->get(Configure::read('AppConfig.htmlHelper')->urlPostDetail('test-post'));
        $this->doAssertHtmlOutput();
    }

    public function testBlogDetail()
    {
        $this->get(Configure::read('AppConfig.htmlHelper')->urlBlogDetail('neuigkeiten'));
        $this->doAssertHtmlOutput();
    }

    public function testPageDetail()
    {
        $this->get(Configure::read('AppConfig.htmlHelper')->urlPageDetail('test-page'));
        $this->doAssertHtmlOutput();
    }

    public function testUsers()
    {
        $this->get(Configure::read('AppConfig.htmlHelper')->urlUsers());
        $this->doAssertHtmlOutput();
    }

    public function testUserProfile()
    {
        $this->get(Configure::read('AppConfig.htmlHelper')->urlUserProfile(1));
        $this->doAssertHtmlOutput();
    }

    public function testWorkshops()
    {
        $this->get(Configure::read('AppConfig.htmlHelper')->urlWorkshops());
        $this->doAssertHtmlOutput();
    }

    public function testStatisticsGlobal()
    {
        $this->get('/widgets/statisticsGlobal');
        $this->doAssertHtmlOutput();
    }

    public function testSkills()
    {
        $this->get(Configure::read('AppConfig.htmlHelper')->urlSkills());
        $this->doAssertHtmlOutput();
    }

    public function testKnowledges()
    {
        $this->get(Configure::read('AppConfig.htmlHelper')->urlKnowledges());
        $this->doAssertHtmlOutput();
    }

    public function testUserBackendLoggedOut()
    {
        $this->get(Configure::read('AppConfig.htmlHelper')->urlUserHome());
        $this->assertResponseCode(302);
        $this->assertRedirectContains('/users/login?redirect=%2Fusers%2Fwelcome');
        $this->assertResponseCode(302);
    }

    public function testUserBackendLoggedIn()
    {
        $this->loginAsRepairhelper();
        $this->get(Configure::read('AppConfig.htmlHelper')->urlUserHome());
        $this->doAssertHtmlOutput();
    }

    public function testSitemap()
    {
        $this->get('/sitemap.xml');
        $this->assertResponseCode(200);
    }

    public function testEventsWithCategoryFilterFound()
    {
        $this->changeEventDate();
        $this->get(Configure::read('AppConfig.htmlHelper')->urlEvents() . '?categories=87');
        $this->doAssertHtmlOutput();
        $this->assertResponseContains('<div class="numbers">1 von insgesamt 2 Terminen gefunden</div>');
    }

    public function testEventsWithCategoryFilterNotFound()
    {
        $this->changeEventDate();
        $this->get(Configure::read('AppConfig.htmlHelper')->urlEvents() . '?categories=88');
        $this->doAssertHtmlOutput();
        $this->assertResponseContains('<div class="numbers">0 von insgesamt 2 Terminen gefunden</div>');
    }

    private function changeEventDate() {
        $eventsTable = $this->getTableLocator()->get('Events');
        $event = $eventsTable->get(6);
        $event->datumstart = Date::now()->addDays(20);
        $eventsTable->save($event);
    }

}
?>