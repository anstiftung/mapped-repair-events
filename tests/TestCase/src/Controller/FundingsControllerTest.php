<?php

namespace App\Test\TestCase\Controller;

use App\Test\TestCase\AppTestCase;
use App\Test\TestCase\Traits\LogFileAssertionsTrait;
use App\Test\TestCase\Traits\LoginTrait;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\Core\Configure;

class FundingsControllerTest extends AppTestCase
{

    use IntegrationTestTrait;
    use LogFileAssertionsTrait;
    use LoginTrait;

    public function setUp(): void {
        parent::setUp();
        $this->resetLogs();
        Configure::write('AppConfig.fundingsEnabled', true);
    }

    public function testRoutesLoggedOut() {
        $this->get(Configure::read('AppConfig.htmlHelper')->urlFundings());
        $this->assertResponseCode(302);
        $this->get(Configure::read('AppConfig.htmlHelper')->urlFundingsEdit(2));
        $this->assertResponseCode(302);
    }

    public function testRoutesAsRepairhelper() {
        $this->loginAsRepairhelper();
        $this->get(Configure::read('AppConfig.htmlHelper')->urlFundings());
        $this->assertResponseCode(302);
        $this->get(Configure::read('AppConfig.htmlHelper')->urlFundingsEdit(2));
        $this->assertResponseCode(302);
    }

    public function testEditWorkshopFundingNotAllowed() {

        $workshopsTable = $this->getTableLocator()->get('Workshops');
        $workshop = $workshopsTable->get(2);
        $workshop->country_code = 'AT';
        $workshopsTable->save($workshop);
        $eventsTable = $this->getTableLocator()->get('Events');
        $event = $eventsTable->get(6);
        $event->datumstart = '2020-01-01';
        $eventsTable->save($event);

        $this->loginAsOrga();
        $this->get(Configure::read('AppConfig.htmlHelper')->urlFundingsEdit(2));
        $this->assertResponseCode(302);
        $this->assertRedirectContains(Configure::read('AppConfig.htmlHelper')->urlFundings());
    }

    public function testEditNotInOrgaTeam() {
        $userWorkshopsTable = $this->getTableLocator()->get('UsersWorkshops');
        $userWorkshop = $userWorkshopsTable->find()->where(['workshop_uid' => 2])->first();
        $userWorkshopsTable->delete($userWorkshop);
        $this->loginAsOrga();
        $this->get(Configure::read('AppConfig.htmlHelper')->urlFundingsEdit(2));
        $this->assertResponseCode(302);
        $this->assertRedirectContains('/users/login?redirect=%2Ffoerderantrag%2Fedit%2F2');
    }

    public function testEditAlreadyCreatedByOtherOwner() {
        $fundingsTable = $this->getTableLocator()->get('Fundings');
        $workshopUid = 2;
        $fundingsTable->save($fundingsTable->newEntity([
            'workshop_uid' => $workshopUid,
            'owner' => 3,
        ]));

        $this->loginAsOrga();
        $this->get(Configure::read('AppConfig.htmlHelper')->urlFundingsEdit($workshopUid));
        $this->assertResponseCode(302);
        $this->assertFlashMessage('Der Förderantrag wurde bereits von einem anderen Nutzer (Max Muster) erstellt.');
        $this->assertRedirectContains(Configure::read('AppConfig.htmlHelper')->urlFundings());
    }

    public function testEditAsOrgaOk() {

        $testWorkshopUid = 2;

        $this->loginAsOrga();
        $this->get(Configure::read('AppConfig.htmlHelper')->urlFundingsEdit($testWorkshopUid));
        $this->assertResponseOk();

        $newName = 'Testname';
        $newStreet = 'Teststraße 1';
        $newZip = '12345';
        $newCity = 'Teststadt';
        $newAdresszusatz = 'Adresszusatz';

        $newSupporterName = 'Supporter Name';

        $newOwnerFirstname = 'Owner Firstname';
        $newOwnerLastname = 'Owner Lastname';
        $newOwnerEmail = 'test@test.at';

        $verifiedFields = [
            'fundings-workshop-name',
        ];

        $this->post(Configure::read('AppConfig.htmlHelper')->urlFundingsEdit($testWorkshopUid), [
            'referer' => '/',
            'Fundings' => [
                'workshop' => [
                    'name' => $newName,
                    'street' => $newStreet . '<script>alert("XSS");</script>',
                    'zip' => $newZip,
                    'city' => $newCity,
                    'adresszusatz' => $newAdresszusatz,
                    'website' => 'non valid string',
                ],
                'supporter' => [
                    'name' => $newSupporterName,
                    'website' => 'orf.at',
                ],
                'owner_user' => [
                    'firstname' => $newOwnerFirstname,
                    'lastname' => $newOwnerLastname,
                    'email' => $newOwnerEmail,
                ],
                'verified_fields' => array_merge($verifiedFields, ['fundings-workshop-website']),
            ]
        ]);
        $this->assertResponseContains('Alle validen Daten wurden erfolgreich gespeichert.');

        $fundingsTable = $this->getTableLocator()->get('Fundings');
        $funding = $fundingsTable->find(contain: ['Workshops', 'OwnerUsers', 'Supporters'])->first();
        $funding->owner_user->revertPrivatizeData();

        $this->assertEquals($verifiedFields, $funding->verified_fields); // must not contain invalid workshops-website

        $this->assertEquals($newName, $funding->workshop->name);
        $this->assertEquals($newStreet, $funding->workshop->street);
        $this->assertEquals('', $funding->workshop->website);
        $this->assertEquals($newZip, $funding->workshop->zip);
        $this->assertEquals($newCity, $funding->workshop->city);
        $this->assertEquals($newAdresszusatz, $funding->workshop->adresszusatz);

        $this->assertEquals($newSupporterName, $funding->supporter->name);
        $this->assertEquals('https://orf.at', $funding->supporter->website);

        $this->assertEquals($newOwnerFirstname, $funding->owner_user->firstname);
        $this->assertEquals($newOwnerLastname, $funding->owner_user->lastname);
        $this->assertEquals($newOwnerEmail, $funding->owner_user->email);

    }

}
?>