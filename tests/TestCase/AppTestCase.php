<?php
namespace App\Test\TestCase;
use Cake\Core\Configure;
use Cake\TestSuite\TestCase;
use Cake\TestSuite\IntegrationTestTrait;

class AppTestCase extends TestCase
{

    use IntegrationTestTrait;

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
        'app.Fundings',
        'app.Fundingbudgetplans',
        'app.Fundingdatas',
        'app.Fundingsupporters',
        'app.Fundinguploads',
        'app.Groups',
        'app.InfoSheets',
        'app.InfoSheetsFormFieldOptions',
        'app.Metatags',
        'app.Pages',
        'app.Posts',
        'app.Photos',
        'app.Provinces',
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

    protected function correctExpectedDate($html, $expectedDate)
    {
        return preg_replace('/\{\{expectedDate\}\}/', str_replace('/', '\\\/', $expectedDate), $html);
    }

    public function setUp(): void
    {
        parent::setUp();
        if (method_exists($this, 'enableSecurityToken')) {

                $this->enableCsrfToken();

                if (!in_array($this->toString(), [
                    'testAddEventsOk',
                ])) {
                    $this->enableSecurityToken();
                }
        }
    }

}


?>