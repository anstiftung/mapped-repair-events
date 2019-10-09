<?php

namespace App\Test\TestCase\Controller;

use App\Test\TestCase\Traits\HtmlOutputAssertionsTrait;
use App\Test\TestCase\Traits\LogFileAssertionsTrait;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

class HtmlOutputTest extends TestCase
{
    use IntegrationTestTrait;
    use HtmlOutputAssertionsTrait;
    use LogFileAssertionsTrait;
    
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
        'app.Skills',
        'app.Users',
        'app.UsersCategories',
        'app.UsersGroups',
        'app.UsersSkills',
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

}
?>