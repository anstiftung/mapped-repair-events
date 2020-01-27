<?php

namespace App\Test\TestCase\Controller;

use App\Test\TestCase\AppTestCase;
use App\Test\TestCase\Traits\HtmlOutputAssertionsTrait;
use App\Test\TestCase\Traits\LogFileAssertionsTrait;
use Cake\Core\Configure;
use Cake\TestSuite\IntegrationTestTrait;

class HtmlOutputTest extends AppTestCase
{
    use IntegrationTestTrait;
    use HtmlOutputAssertionsTrait;
    use LogFileAssertionsTrait;
    
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

    public function testEvents()
    {
        $this->get(Configure::read('AppConfig.htmlHelper')->urlEvents());
        $this->doAssertHtmlOutput();
        $this->assertResponseContains('<div class="numbers">1 Termin gefunden</div>');
        $this->assertResponseContains('href="/test-workshop?event=6,2040-01-01#datum"');
    }
}
?>