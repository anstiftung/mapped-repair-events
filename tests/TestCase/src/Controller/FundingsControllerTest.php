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
        $this->get(Configure::read('AppConfig.htmlHelper')->urlFunding());
        $this->assertResponseCode(302);
        $this->get(Configure::read('AppConfig.htmlHelper')->urlFundingEdit(2));
        $this->assertResponseCode(302);
    }

    public function testRoutesAsRepairhelper() {
        $this->loginAsRepairhelper();
        $this->get(Configure::read('AppConfig.htmlHelper')->urlFunding());
        $this->assertResponseCode(302);
        $this->get(Configure::read('AppConfig.htmlHelper')->urlFundingEdit(2));
        $this->assertResponseCode(302);
    }

    public function testEditOk() {
        $this->loginAsOrga();
        $this->get(Configure::read('AppConfig.htmlHelper')->urlFundingEdit(2));
        $this->assertResponseOk();
    }

    public function testEditNotOk() {
        
        $workshopsTable = $this->getTableLocator()->get('Workshops');
        $workshop = $workshopsTable->get(2);
        $workshop->country_code = 'AT';
        $workshopsTable->save($workshop);
        $eventsTable = $this->getTableLocator()->get('Events');
        $event = $eventsTable->get(6);
        $event->datumstart = '2020-01-01';
        $eventsTable->save($event);

        $this->loginAsOrga();
        $this->get(Configure::read('AppConfig.htmlHelper')->urlFundingEdit(2));
        $this->assertResponseCode(302);
        $this->assertRedirectContains(Configure::read('AppConfig.htmlHelper')->urlFunding());
    }

    public function testEditNotInOrgaTeam() {
        $userWorkshopsTable = $this->getTableLocator()->get('UsersWorkshops');
        $userWorkshop = $userWorkshopsTable->find()->where(['workshop_uid' => 2])->first();
        $userWorkshopsTable->delete($userWorkshop);
        $this->loginAsOrga();
        $this->get(Configure::read('AppConfig.htmlHelper')->urlFundingEdit(2));
        $this->assertResponseCode(302);
        $this->assertRedirectContains('/users/login?redirect=%2Ffoerderantrag%2Fedit%2F2');
    }

    public function testEditAsOrgaOk() {

        $testWorkshopUid = 2;

        $this->loginAsOrga();
        $this->get(Configure::read('AppConfig.htmlHelper')->urlFundingEdit($testWorkshopUid));
        $this->assertResponseOk();

        $newName = 'Testname';
        $newStreet = 'Teststraße 1';
        $newZip = '12345';
        $newCity = 'Teststadt';
        $newAdresszusatz = 'Adresszusatz';

        $this->post(Configure::read('AppConfig.htmlHelper')->urlFundingEdit($testWorkshopUid), [
            'referer' => '/',
            'Workshops' => [
                'name' => $newName,
                'street' => $newStreet,
                'zip' => $newZip,
                'city' => $newCity,
                'adresszusatz' => $newAdresszusatz
            ]
        ]);
        $this->assertResponseNotContains('error');
        $this->assertResponseContains('Förderantrag erfolgreich gespeichert.');

        $workshopsTable = $this->getTableLocator()->get('Workshops');
        $workshop = $workshopsTable->get($testWorkshopUid);
        $this->assertEquals($newName, $workshop->name);
        $this->assertEquals($newStreet, $workshop->street);
        $this->assertEquals($newZip, $workshop->zip);
        $this->assertEquals($newCity, $workshop->city);
        $this->assertEquals($newAdresszusatz, $workshop->adresszusatz);

    }

}
?>