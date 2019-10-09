<?php

namespace App\Test\TestCase\Controller;

use App\Test\TestCase\Traits\PageErrorAssertionsTrait;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

class PagesControllerTest extends TestCase
{
    use IntegrationTestTrait;
    use PageErrorAssertionsTrait;
    
    public $fixtures = [
        'app.Categories',
        'app.Countries',
        'app.Events',
        'app.Groups',
        'app.InfoSheets',
        'app.Metatags',
        'app.Pages',
        'app.Posts',
        'app.Users',
        'app.UsersGroups',
        'app.UsersWorkshops',
        'app.Worknews',
        'app.Workshops',
        'app.WorkshopsCategories'
    ];
    
    public function testAllPublicUrls()
    {
        $testUrls = [
            '/',
            Configure::read('AppConfig.htmlHelper')->urlWorkshopDetail('test-workshop')
        ];
        foreach($testUrls as $testUrl) {
            $this->get($testUrl);
            $this->doAssertPagesForErrors($testUrls);
        }
    }
}
?>