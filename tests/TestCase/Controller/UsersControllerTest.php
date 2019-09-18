<?php

namespace App\Test\TestCase\Controller;

use App\Test\Fixture\UsersFixture;
use App\Test\TestCase\Traits\UserAssertionsTrait;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

class UsersControllerTest extends TestCase
{
    use IntegrationTestTrait;
    use UserAssertionsTrait;
    
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
        $this->doUserPrivacyAssertions();
        $users = $this->viewVariable('users');
        $this->assertEquals(2, count($users));
    }
    
    public function testPublicProfileFieldsPrivate()
    {
        $this->get('/users/profile/1');
        $this->doUserPrivacyAssertions();
        $this->assertResponseContains('<h1>JohnDoe</h1>');
        $this->assertResponseNotContains('Weitere Kontaktmöglichkeiten');
        $this->assertResponseNotContains('my-additional@email.com');
        $this->assertResponseNotRegExp('`'.preg_quote('[javascript protected email address]</span>').'`');
    }
    
    public function testPublicProfileFieldsNotPrivate()
    {
        $this->get('/users/profile/3');
        $this->assertResponseContains('<h1>MaxMuster</h1>');
        $this->assertResponseContains('<span class="public-name-wrapper">Max Muster</span>');
        $this->assertResponseRegExp('`'.preg_quote('[javascript protected email address]</span>').'`');
        $this->assertResponseContains('<span class="address-wrapper">Test Street 4, 66546</span>');
        $this->assertResponseContains('>Weitere Kontaktmöglichkeiten</b><ul><li>my-additional@email.com</li>');
    }
    
}
?>