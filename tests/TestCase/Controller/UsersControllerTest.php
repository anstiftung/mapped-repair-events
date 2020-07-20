<?php

namespace App\Test\TestCase\Controller;

use App\Test\TestCase\AppTestCase;
use App\Test\TestCase\Traits\LogFileAssertionsTrait;
use App\Test\TestCase\Traits\UserAssertionsTrait;
use Cake\Core\Configure;
use Cake\TestSuite\EmailTrait;
use Cake\TestSuite\IntegrationTestTrait;

class UsersControllerTest extends AppTestCase
{
    use IntegrationTestTrait;
    use UserAssertionsTrait;
    use EmailTrait;
    use LogFileAssertionsTrait;

    private $validUserData = [
        'nick' => 'JohnDoeA<img onerror="alert();" />',
        'firstname' => 'John<img onerror="alert();" />',
        'lastname' => 'DoeA',
        'zip' => '12345',
        'email' => 'johndoeA@mailinator.com',
        'privacy_policy_accepted' => 1
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
        $this->post(
            Configure::read('AppConfig.htmlHelper')->urlRegisterOrga(),
            [
                'antiSpam' => 100,
                'Users' => $this->validUserData
            ]
        );

        $this->assertRedirectContains('/');

        $user = $this->getRegisteredUser();

        $this->assertEquals($user->uid, 8);
        $this->assertEquals($user->nick, 'JohnDoeA');
        $this->assertEquals($user->email, $this->validUserData['email']);
        $this->assertEquals($user->firstname, 'John');
        $this->assertEquals(count($user->groups), 1);
        $this->assertNotEquals($user->password, null);
        $this->assertEquals($user->groups[0]->id, GROUPS_ORGA);
        $this->assertNotEquals($user->groups[0]->id, GROUPS_REPAIRHELPER);

        $this->assertMailCount(1);
        $this->assertMailSentTo($this->validUserData['email']);
        $this->assertMailContains('Passwort');

        $this->get('/users/activate/' . $user->confirm);
        $user = $this->getRegisteredUser();

        $this->assertEquals($user->confirm, 'ok');
        $this->assertRedirectContains(Configure::read('AppConfig.htmlHelper')->urlUserHome());

    }

    public function testRegisterValidationsNoData()
    {
        $this->post(
            Configure::read('AppConfig.htmlHelper')->urlRegisterOrga(),
            [
                'antiSpam' => 100
            ]
        );
        $this->assertEmptyData();
        $this->assertNoRedirect();
    }

    public function testRegisterValidationsEmptyData()
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
        $this->assertEmptyData();
        $this->assertNoRedirect();
    }

    public function testRegisterValidationsEmail()
    {
        $this->post(
            Configure::read('AppConfig.htmlHelper')->urlRegisterOrga(),
            [
                'antiSpam' => 100,
                'Users' => [
                    'email' => 'johndoe@mailinator.com',
                ]
            ]
        );
        $this->assertResponseContains('Diese E-Mail-Adresse wird bereits verwendet.');
        $this->assertNoRedirect();
    }

    public function testRegisterValidationsMxRecord()
    {
        $this->post(
            Configure::read('AppConfig.htmlHelper')->urlRegisterOrga(),
            [
                'antiSpam' => 100,
                'Users' => [
                    'email' => 'johndoe@gadsfadsewcadfaees.com',
                ]
            ]
        );
        $this->assertResponseContains('Bitte trage eine gültige E-Mail-Adresse ein.');
        $this->assertNoRedirect();
    }

    public function testRegisterNewsletter()
    {
        $this->validUserData['i_want_to_receive_the_newsletter'] = 1;
        $this->post(
            Configure::read('AppConfig.htmlHelper')->urlRegisterOrga(),
            [
                'antiSpam' => 100,
                'Users' => $this->validUserData
            ]
        );
        $newsletter = $this->getNewsletterData();
        $this->assertEquals(1, count($newsletter));
        $this->assertNotEquals('ok', $newsletter[0]->confirm);

        $user = $this->getRegisteredUser();
        $this->get('/users/activate/' . $user->confirm);

        $newsletter = $this->getNewsletterData();
        $this->assertEquals('ok', $newsletter[0]->confirm);
        $this->assertMailCount(2);

    }

    private function getNewsletterData()
    {
        $this->Newsletter = $this->getTableLocator()->get('Newsletters');
        $newsletter = $this->Newsletter->find('all', [
            'conditions' => [
                'Newsletters.email' => $this->validUserData['email']
            ]
        ])->toArray();
        return $newsletter;
    }

    private function getRegisteredUser()
    {
        $this->User = $this->getTableLocator()->get('Users');
        $user = $this->User->find('all', [
            'conditions' => [
                'Users.email' => $this->validUserData['email']
            ],
            'contain' => [
                'Groups'
            ]
        ])->first();
        $user->revertPrivatizeData();
        return $user;
    }

    private function assertEmptyData()
    {
        $this->assertResponseContains('Bitte trage deinen Nickname ein.');
        $this->assertResponseContains('Bitte trage deinen Vornamen ein.');
        $this->assertResponseContains('Bitte trage deinen Nachnamen ein.');
        $this->assertResponseContains('Bitte trage deine PLZ ein.');
        $this->assertResponseContains('Bitte akzeptiere die Datenschutzbestimmungen.');
    }

}
?>