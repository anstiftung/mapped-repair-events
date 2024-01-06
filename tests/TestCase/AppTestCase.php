<?php
namespace App\Test\TestCase;
use Cake\Core\Configure;
use Cake\TestSuite\TestCase;

class AppTestCase extends TestCase
{

    protected array $fixtures = [
        'app.BlockedWorkshopSlugs',
        'app.Blogs',
        'app.Brands',
        'app.Categories',
        'app.Countries',
        'app.Events',
        'app.EventsCategories',
        'app.FormFields',
        'app.FormFieldOptions',
        'app.Groups',
        'app.InfoSheets',
        'app.InfoSheetsFormFieldOptions',
        'app.Metatags',
        'app.Pages',
        'app.Posts',
        'app.Photos',
        'app.Roots',
        'app.Skills',
        'app.ThirdPartyStatistics',
        'app.Users',
        'app.UsersCategories',
        'app.UsersGroups',
        'app.UsersSkills',
        'app.UsersWorkshops',
        'app.Worknews',
        'app.Workshops',
        'app.WorkshopsCategories',
    ];

    protected function correctServerName($html)
    {
        return preg_replace('/\{\{serverName\}\}/', str_replace('/', '\\\/', Configure::read('AppConfig.serverName')), $html);
    }

    public function setUp(): void
    {
        parent::setUp();
        if (method_exists($this, 'enableSecurityToken')) {

                $this->enableCsrfToken();
                
                if (!in_array($this->getName(), [
                    'testAddEventsOk',
                ])) {
                    $this->enableSecurityToken();
                }
        }
    }

}


?>