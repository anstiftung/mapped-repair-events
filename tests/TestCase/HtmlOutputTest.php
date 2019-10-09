<?php

namespace App\Test\TestCase\Controller;

use App\Test\TestCase\Traits\HtmlOutputAssertionsTrait;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

class HtmlOutputTest extends TestCase
{
    use IntegrationTestTrait;
    use HtmlOutputAssertionsTrait;
    
    public $fixtures = [
        'app.Blogs',
        'app.Categories',
        'app.Countries',
        'app.Events',
        'app.Groups',
        'app.InfoSheets',
        'app.Metatags',
        'app.Pages',
        'app.Photos',
        'app.Posts',
        'app.Users',
        'app.UsersGroups',
        'app.UsersWorkshops',
        'app.Worknews',
        'app.Workshops',
        'app.WorkshopsCategories'
    ];
    
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

}
?>