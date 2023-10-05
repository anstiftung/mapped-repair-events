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

    private $Group;
    private $User;
    private $UsersWorkshop;
    private $Workshop;

    private $validUserData = [
        'nick' => 'JohnDoeA<img onerror="alert();" />',
        'firstname' => 'John<img onerror="alert();" />',
        'lastname' => 'DoeA',
        'zip' => '12345',
        'email' => 'johndoeA@mailinator.com',
        'privacy_policy_accepted' => 1,
        'categories' => [
            '_ids' => [87],
        ],
        'skills' => [
            '_ids' => [1, 'new skill'],
        ],
    ];

    public function testAll()
    {
        $this->get(Configure::read('AppConfig.htmlHelper')->urlUsers());
        $this->doUserPrivacyAssertions();
        $users = $this->viewVariable('users');
        $this->assertEquals(3, count($users));
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
        $this->assertResponseNotContains('<h1>MaxMuster</h1>');
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
                'Users' => $this->validUserData,
            ]
        );

        $this->assertRedirectContains('/');

        $user = $this->getRegisteredUser();
        $this->assertEquals($user->uid, 9);
        $this->assertEquals($user->nick, 'JohnDoeA');
        $this->assertEquals($user->email, $this->validUserData['email']);
        $this->assertEquals($user->firstname, 'John');
        $this->assertEquals(count($user->groups), 1);
        $this->assertNotEquals($user->password, null);
        $this->assertEquals($user->groups[0]->id, GROUPS_ORGA);
        $this->assertNotEquals($user->groups[0]->id, GROUPS_REPAIRHELPER);
        $this->assertEquals($user->categories[0]->id, 87);
        $this->assertEquals($user->skills[0]->id, 1);
        $this->assertEquals($user->skills[1]->id, 2);
        $this->assertEquals($user->skills[1]->name, 'new skill');

        $this->assertMailCount(1);
        $this->assertMailSentTo($this->validUserData['email']);
        $this->assertMailContains('Passwort');

        $this->get('/users/activate/' . $user->confirm);
        $user = $this->getRegisteredUser();

        $this->assertEquals($user->confirm, 'ok');
        $this->assertRedirectContains(Configure::read('AppConfig.htmlHelper')->urlUserHome());

    }

    public function testRegisterValidationsEmail()
    {
        $this->post(
            Configure::read('AppConfig.htmlHelper')->urlRegisterOrga(),
            [
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
                'Users' => [
                    'email' => 'johndoe@gadsfadsewcadfaees.com',
                ]
            ]
        );
        $this->assertResponseContains('Bitte trage eine gültige E-Mail-Adresse ein.');
        $this->assertNoRedirect();
    }

    public function testDeleteRepairhelperUserWithNonDeletedWorkshop()
    {
        $userUid = 1;
        $this->User = $this->getTableLocator()->get('Users');
        $this->Workshop = $this->getTableLocator()->get('Workshops');
        $this->Group = $this->getTableLocator()->get('Groups');
        $user = $this->User->get($userUid, [
            'contain' => [
                'Groups',
                'OwnerWorkshops',
            ],
        ]);
        // change user to repairhelper
        $user->groups = [
            $this->Group->get(GROUPS_REPAIRHELPER)
        ];
        $this->User->save($user);

        // 1. try to delete user with workshop relation
        $this->User->delete($user);
        $this->assertFalse($user->hasErrors());

        // 3. check changed owner of workshops
        $workshop = $this->Workshop->get($user->owner_workshops[0]->uid);
        $this->assertEquals(8, $workshop->owner);

        // 4. check successfully deleted user
        $user = $this->User->find('all', [
            'conditions' => [
                'Users.uid' => $userUid,
            ],
        ])->first();
        $this->assertEmpty($user);
    }

    public function testDeleteLastOrgaUserWithNonDeletedWorkshop()
    {

        $userUid = 1;
        $this->User = $this->getTableLocator()->get('Users');
        $this->Workshop = $this->getTableLocator()->get('Workshops');
        $user = $this->User->get($userUid);

        // 1. try to delete user with workshop relation
        $this->User->delete($user);
        $this->assertArrayHasKey('workshops', $user->getErrors());
        $this->assertEquals('Der User ist bei folgenden Initiativen als letzte(r) Organisator*in zugeordnet: Test Workshop', $user->getErrors()['workshops'][0]);

        // 2. manually remove workshop relation
        $ownerAndAssociatedWorkshopId = 2;
        $this->loginAsAdmin();
        $this->get(Configure::read('AppConfig.htmlHelper')->urlUserWorkshopResign('user', $userUid, $ownerAndAssociatedWorkshopId));

        // 3. successfully delete user
        $user = $this->User->get($userUid, [
            'contain' => [
                'OwnerWorkshops',
            ]
        ]);
        $this->User->delete($user);

        // 4. check changed owner of workshops
        $workshop = $this->Workshop->get($user->owner_workshops[0]->uid);
        $this->assertEquals(8, $workshop->owner);

        $user = $this->User->find('all', [
            'conditions' => [
                'Users.uid' => $userUid,
            ],
        ])->first();
        $this->assertEmpty($user);

    }

    public function testDeleteLastOrgaUserWithDeletedWorkshop()
    {

        $userUid = 1;
        $workshopUid = 2;
        $this->User = $this->getTableLocator()->get('Users');
        $this->Workshop = $this->getTableLocator()->get('Workshops');

        $workshop = $this->Workshop->get($workshopUid);
        $workshop->status = APP_DELETED;
        $this->Workshop->save($workshop);

        $user = $this->User->get($userUid);

        // delete user with workshop (status deleted) relation
        $this->User->delete($user);
        $this->assertFalse($user->hasErrors());

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

        // approve if user is deleted
        $user = $this->User->find('all', [
            'conditions' => [
                'Users.uid' => $userUid,
            ],
        ])->first();
        $this->assertEmpty($user);

    }

    private function getRegisteredUser()
    {
        $this->User = $this->getTableLocator()->get('Users');
        $user = $this->User->find('all', [
            'conditions' => [
                'Users.email' => $this->validUserData['email']
            ],
            'contain' => [
                'Groups',
                'Categories',
                'Skills',
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