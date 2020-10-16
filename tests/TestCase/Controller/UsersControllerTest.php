<?php

namespace App\Test\TestCase\Controller;

use App\Test\TestCase\AppTestCase;
use App\Test\TestCase\Traits\LogFileAssertionsTrait;
use App\Test\TestCase\Traits\UserAssertionsTrait;
use Cake\Core\Configure;
use Cake\TestSuite\EmailTrait;
use Cake\TestSuite\IntegrationTestTrait;
use App\Test\TestCase\Traits\LoginTrait;

class UsersControllerTest extends AppTestCase
{
    use IntegrationTestTrait;
    use UserAssertionsTrait;
    use EmailTrait;
    use LogFileAssertionsTrait;
    use LoginTrait;

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

    public function testDeleteUserWithNonDeletedWorkshop()
    {
        $userUid = 1;
        $this->User = $this->getTableLocator()->get('Users');
        $this->Workshop = $this->getTableLocator()->get('Workshops');
        $user = $this->User->get($userUid);

        // 1. try to delete user with workshop relation
        $this->User->delete($user);
        $this->assertArrayHasKey('workshops', $user->getErrors());
        $this->assertEquals('Der User ist noch bei folgenden Initiativen als Mitarbeiter zugeordnet: Test Workshop', $user->getErrors()['workshops'][0]);

        $this->assertArrayHasKey('owner_workshops', $user->getErrors());
        $this->assertEquals('Der User ist noch bei folgenden Initiativen als Owner zugeordnet: Test Workshop', $user->getErrors()['owner_workshops'][0]);

        // 2. manually remove workshop relation
        $ownerAndAssociatedWorkshopId = 2;
        $this->loginAsOrga();
        $this->get(Configure::read('AppConfig.htmlHelper')->urlUserWorkshopResign('user', $userUid, $ownerAndAssociatedWorkshopId));

        // 3. manually remove ownership
        $workshop = $this->Workshop->get($ownerAndAssociatedWorkshopId);
        $workshop->owner = 0;
        $this->Workshop->save($workshop);

        // 4. successfully delete user
        $user = $this->User->get($userUid);
        $this->User->delete($user);
        $user = $this->User->find('all', [
            'conditions' => [
                'Users.uid' => $userUid,
            ],
        ])->first();
        $this->assertEmpty($user);
    }

    public function testDeleteUserWithDeletedWorkshop()
    {
        $userUid = 1;
        $workshopUid = 2;
        $this->User = $this->getTableLocator()->get('Users');
        $this->Workshop = $this->getTableLocator()->get('Workshops');

        $workshop = $this->Workshop->get($workshopUid);
        $workshop->status = APP_DELETED;
        $this->Workshop->save($workshop);

        $user = $this->User->get($userUid);

        // 1. delete user with workshop (status deleted) relation
        $this->User->delete($user);

        $this->assertEmpty($user->getErrors());

        // check if workshop owner of deleted workshops (-1) were set to 0 automatically
        $workshop = $this->Workshop->get($workshopUid);
        $this->assertEquals(0, $workshop->owner);

        // check if all workshop associations the user were removed automatically
        $this->UsersWorkshop = $this->getTableLocator()->get('UsersWorkshops');
        $usersWorkshops = $this->UsersWorkshop->find('all', [
            'conditions' => [
                'UsersWorkshops.user_uid' => $userUid,
            ],
        ])->toArray();
        $this->assertEmpty($usersWorkshops);

        // 4. check last orga

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