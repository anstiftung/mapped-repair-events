<?php

namespace App\Test\TestCase\Controller;

use App\Test\Fixture\UsersFixture;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

class UsersControllerTest extends TestCase
{
    use IntegrationTestTrait;

    public $fixtures = [
        'app.Categories',
        'app.Groups',
        'app.Pages',
        'app.Skills',
        'app.Users',
        'app.UsersCategories',
        'app.UsersGroups',
        'app.UsersSkills',
        'app.UsersWorkshops',
        'app.Workshops'
    ];

    public function testAll()
    {
        $this->get('/aktive');
        $this->doUserAssertions();
        $users = $this->viewVariable('users');
        $this->assertEquals(1, count($users));
    }
    
    public function testPublicProfile()
    {
        $this->get('/users/profile/1');
        $this->doUserAssertions();
    }
    
    private function doUserAssertions()
    {
        $this->assertResponseOk();
        $this->assertResponseNotEmpty();
        $this->assertResponseContains('<span class="public-name-wrapper">John</span>');
        $this->assertResponseNotContains('<span class="public-name-wrapper">John Doe</span>');
    }
    
}
?>