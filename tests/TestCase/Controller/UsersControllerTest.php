<?php

namespace App\Test\TestCase\Controller;

use App\Test\TestCase\Traits\UserAssertionsTrait;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\EmailTrait;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

class UsersControllerTest extends TestCase
{
    use IntegrationTestTrait;
    use UserAssertionsTrait;
    use EmailTrait;
    
    public $fixtures = [
        'app.Categories',
        'app.Countries',
        'app.Groups',
        'app.Pages',
        'app.Roots',
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
        $this->get(Configure::read('AppConfig.htmlHelper')->urlUsers());
        $this->doUserPrivacyAssertions();
        $users = $this->viewVariable('users');
        $this->assertEquals(2, count($users));
    }
    
    public function testPublicProfileFieldsPrivate()
    {
        $this->get(Configure::read('AppConfig.htmlHelper')->urlUserProfile(1));
        $this->doUserPrivacyAssertions();
        $this->assertResponseContains('<h1>JohnDoe</h1>');
        $this->assertResponseNotContains('Weitere Kontaktmöglichkeiten');
        $this->assertResponseNotContains('my-additional@email.com');
        $this->assertResponseNotRegExp('`'.preg_quote('[javascript protected email address]</span>').'`');
    }
    
    public function testPublicProfileFieldsNotPrivate()
    {
        $this->get(Configure::read('AppConfig.htmlHelper')->urlUserProfile(3));
        $this->assertResponseContains('<h1>MaxMuster</h1>');
        $this->assertResponseContains('<span class="public-name-wrapper">Max Muster</span>');
        $this->assertResponseRegExp('`'.preg_quote('[javascript protected email address]</span>').'`');
        $this->assertResponseContains('<span class="address-wrapper">Test Street 4, 66546</span>');
        $this->assertResponseContains('>Weitere Kontaktmöglichkeiten</b><ul><li>my-additional@email.com</li>');
    }
    
    public function testRegisterOrga()
    {
        $uniqueEmail = 'johndoeA@example.com';
        $this->post(
            Configure::read('AppConfig.htmlHelper')->urlRegisterOrga(),
            [
                'antiSpam' => 100,
                'Users' => [
                    'nick' => 'JohnDoeA',
                    'firstname' => 'John',
                    'lastname' => 'DoeA',
                    'email' => $uniqueEmail
                ]
            ]
        );
        
        $this->assertRedirectContains('/');
        
        $this->User = TableRegistry::getTableLocator()->get('Users');
        $user = $this->User->find('all', [
            'conditions' => [
                'Users.email' => $uniqueEmail
            ],
            'contain' => [
                'Groups'
            ]
        ])->first();
        
        $this->assertEquals($user->uid, 4);
        $this->assertEquals(count($user->groups), 1);
        $this->assertEquals($user->groups[0]->id, GROUPS_ORGA);
        $this->assertNotEquals($user->groups[0]->id, GROUPS_REPAIRHELPER);
        
        $this->assertMailCount(1);
        $this->assertMailSentTo($uniqueEmail);
        $this->assertMailContains('Passwort');
        
    }
    
    public function testRegisterValidationsEmpty()
    {
        $this->post(
            Configure::read('AppConfig.htmlHelper')->urlRegisterOrga(),
            [
                'antiSpam' => 100,
                'Users' => [
                    'nick' => '',
                    'firstname' => '',
                    'lastname' => '',
                    'email' => '',
                    'zip' => '',
                    'privacy_policy_accepted' => 0
                ]
            ]
        );
        $this->assertResponseContains('Bitte trage deinen Nickname ein.');
        $this->assertResponseContains('Bitte trage deinen Vornamen ein.');
        $this->assertResponseContains('Bitte trage deinen Nachnamen ein.');
        $this->assertResponseContains('Bitte trage deine E-Mail-Adresse ein.');
        $this->assertResponseContains('Bitte trage deine PLZ ein.');
        $this->assertResponseContains('Bitte akzeptiere die Datenschutzbestimmungen.');
        $this->assertNoRedirect();
    }

    public function testRegisterValidationsEmail()
    {
        $this->post(
            Configure::read('AppConfig.htmlHelper')->urlRegisterOrga(),
            [
                'antiSpam' => 100,
                'Users' => [
                    'email' => 'johndoe@example.com',
                ]
            ]
        );
        $this->assertResponseContains('Diese E-Mail-Adresse wird bereits verwendet.');
        $this->assertNoRedirect();
    }
    
}
?>