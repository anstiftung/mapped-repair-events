<?php

namespace App\Test\TestCase\Controller;

use App\Model\Entity\Fundingbudgetplan;
use App\Test\TestCase\AppTestCase;
use App\Test\TestCase\Traits\LogFileAssertionsTrait;
use App\Test\TestCase\Traits\LoginTrait;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\Core\Configure;
use Cake\Event\EventInterface;
use App\Test\Mock\GeoServiceMock;
use Cake\Controller\Controller;
use App\Model\Table\FundingsTable;
use Laminas\Diactoros\UploadedFile;
use App\Model\Entity\Fundingupload;
use App\Test\Fixture\UsersFixture;

class FundingsControllerTest extends AppTestCase
{

    use IntegrationTestTrait;
    use LogFileAssertionsTrait;
    use LoginTrait;

	public function controllerSpy(EventInterface $event, ?Controller $controller = null): void
    {
		parent::controllerSpy($event, $controller);
		$this->_controller->geoService = new GeoServiceMock();
	}

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
        $this->assertRedirectContains('/users/login');
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

    private function prepareWorkshopForFunding($workshopUid) {
        // add 4 events for 2025 (required for funding)
        $eventsTable = $this->getTableLocator()->get('Events');
        $i = 0;
        while($i<4) {
            $event = $eventsTable->newEntity([
                'workshop_uid' => $workshopUid,
                'datumstart' => '2025-01-01',
                'status' => APP_ON,
                'created' => '2020-01-01 00:00:00',
            ]);
            $eventsTable->save($event);
            $i++;
        }
    }

    public function testEditAsOrgaOk() {

        $fundingsTable = $this->getTableLocator()->get('Fundings');
        $testWorkshopUid = 2;
        $this->loginAsOrga();
        $this->prepareWorkshopForFunding($testWorkshopUid);

        $this->get(Configure::read('AppConfig.htmlHelper')->urlFundingsEdit($testWorkshopUid));
        $this->assertResponseOk();

        $newName = 'Testname';
        $newStreet = 'Teststraße 1';
        $newZip = '12345';
        $newCity = 'Teststadt';
        $newAdresszusatz = 'Adresszusatz';

        $newFundingsupporterName = 'Fundingsupporter Name';

        $newOwnerFirstname = 'Owner Firstname';
        $newOwnerLastname = 'Owner Lastname';
        $newOwnerEmail = 'test@test.at';

        $newFundingdataDescription = 'Fundingdata Description';

        $newFundingbudgetplanDescriptionOk = 'Fundingdata Description Ok';
        $newFundingbudgetplanAmountOk = 99;

        $verifiedFields = [
            'fundings-workshop-name',
        ];

        $testWorkshop = [
            'name' => $newName,
            'street' => $newStreet . '<script>alert("XSS");</script>',
            'zip' => $newZip,
            'city' => $newCity,
            'adresszusatz' => $newAdresszusatz,
            'website' => 'non valid string',
            'use_custom_coordinates' => 0,
        ];

        $testFundingsupporter = [
            'name' => $newFundingsupporterName,
            'website' => 'orf.at',
        ];

        $testFundingdata = [
            'description' => $newFundingdataDescription,
        ];

        $testOwnerUser = [
            'firstname' => $newOwnerFirstname,
            'lastname' => $newOwnerLastname,
            'email' => $newOwnerEmail,
            'use_custom_coordinates' => 0,
        ];


        $uploadTemplateJpgFile = TESTS . 'files/test.jpg';
        $uploadTemplateTxtFile = TESTS . 'files/test.txt';
        $uploadFileActivityProof1 = TESTS . 'files/uploadActivityProof1.jpg';
        $uploadFileActivityProof2 = TESTS . 'files/uploadActivityProof2.txt';
        $uploadFileFreistellungsbescheid1 = TESTS . 'files/uploadTFreistellungsbescheid1.jpg';
        $uploadFileFreistellungsbescheid2 = TESTS . 'files/uploadFreistellungsbescheid2.jpg';
        copy($uploadTemplateJpgFile, $uploadFileActivityProof1);
        copy($uploadTemplateTxtFile, $uploadFileActivityProof2);
        copy($uploadTemplateJpgFile, $uploadFileFreistellungsbescheid1);
        copy($uploadTemplateJpgFile, $uploadFileFreistellungsbescheid2);

        // 1) POST
        $this->post(Configure::read('AppConfig.htmlHelper')->urlFundingsEdit($testWorkshopUid), [
            'referer' => '/',
            'Fundings' => [
                'workshop' => $testWorkshop,
                'fundingsupporter' => $testFundingsupporter,
                'fundingdata' => $testFundingdata,
                'owner_user' => $testOwnerUser,
                'verified_fields' => array_merge($verifiedFields, ['fundings-workshop-website']),
                'files_fundinguploads_activity_proofs' => [
                    new UploadedFile(
                        $uploadFileActivityProof1,
                        filesize($uploadFileActivityProof1),
                        UPLOAD_ERR_OK,
                        'test.jpg',
                        'image/jpeg',
                    ),
                ],
                'files_fundinguploads_freistellungsbescheids' => [
                    new UploadedFile(
                        $uploadFileFreistellungsbescheid1,
                        filesize($uploadFileFreistellungsbescheid1),
                        UPLOAD_ERR_OK,
                        'test.jpg',
                        'image/jpeg',
                    ),
                ],
                'fundingbudgetplans' => [
                    [
                        'id' => 1,
                        'type' => Fundingbudgetplan::TYPE_A,
                        'description' => $newFundingbudgetplanDescriptionOk,
                        'amount' => $newFundingbudgetplanAmountOk,
                    ],
                    [
                        'id' => 2,
                        'type' => '', // invalid
                        'description' => $newFundingbudgetplanDescriptionOk,
                        'amount' => $newFundingbudgetplanAmountOk,
                    ],
                    [
                        'id' => 3,
                        'type' => Fundingbudgetplan::TYPE_B,
                        'description' => 'abc', // invalid
                        'amount' => $newFundingbudgetplanAmountOk,
                    ],
                    [
                        'id' => 4,
                        'type' => Fundingbudgetplan::TYPE_C,
                        'description' => $newFundingbudgetplanDescriptionOk,
                        'amount' => -1, // invalid
                    ],
                ],
            ]
        ]);
        $this->assertResponseContains('Der Förderantrag wurde erfolgreich zwischengespeichert.');

        $fundingUid = $fundingsTable->find()->first()->uid;
        $funding = $fundingsTable->getUnprivatizedFundingWithAllAssociations($fundingUid);

        $this->assertEquals($verifiedFields, $funding->verified_fields); // must not contain invalid workshops-website

        $this->assertEquals($newName, $funding->workshop->name);
        $this->assertEquals($newStreet, $funding->workshop->street);
        $this->assertEquals('', $funding->workshop->website);
        $this->assertEquals($newZip, $funding->workshop->zip);
        $this->assertEquals($newCity, $funding->workshop->city);
        $this->assertEquals($newAdresszusatz, $funding->workshop->adresszusatz);

        $this->assertEquals($newFundingsupporterName, $funding->fundingsupporter->name);
        $this->assertEquals('https://orf.at', $funding->fundingsupporter->website);

        $this->assertEquals($newOwnerFirstname, $funding->owner_user->firstname);
        $this->assertEquals($newOwnerLastname, $funding->owner_user->lastname);
        $this->assertEquals($newOwnerEmail, $funding->owner_user->email);
        $this->assertTextEndsWith('street,phone,city', $funding->owner_user->private);

        $this->assertEquals($newFundingdataDescription, $funding->fundingdata->description);

        $this->assertCount(1, $funding->fundinguploads_activity_proofs);
        foreach($funding->fundinguploads_activity_proofs as $fundingupload) {
            $this->assertEquals(Fundingupload::TYPE_ACTIVITY_PROOF, $fundingupload->type);
            $this->assertFileExists($fundingupload->full_path);
        }

        $this->assertCount(1, $funding->fundinguploads_freistellungsbescheids);
        foreach($funding->fundinguploads_freistellungsbescheids as $fundingupload) {
            $this->assertEquals(Fundingupload::TYPE_FREISTELLUNGSBESCHEID, $fundingupload->type);
            $this->assertFileExists($fundingupload->full_path);
        }

        $this->assertEquals(FundingsTable::FUNDINGBUDGETPLANS_COUNT, count($funding->fundingbudgetplans));
        $this->assertEquals(Fundingbudgetplan::TYPE_A, $funding->fundingbudgetplans[0]->type);
        $this->assertEquals($newFundingbudgetplanDescriptionOk, $funding->fundingbudgetplans[0]->description);
        $this->assertEquals($newFundingbudgetplanAmountOk, $funding->fundingbudgetplans[0]->amount);

        $emptyFundingbudgets = [2, 3, 4];
        foreach($funding->fundingbudgetplans as $fundingbudgetplan) {
            if (!in_array($fundingbudgetplan->id, $emptyFundingbudgets)) {
                continue;
            }
            $this->assertFalse($fundingbudgetplan->is_valid);
            $this->assertFalse($fundingbudgetplan->is_not_empty);
        }

        // 2) POST test upload validations
        $this->post(Configure::read('AppConfig.htmlHelper')->urlFundingsEdit($testWorkshopUid), [
            'referer' => '/',
            'Fundings' => [
                'workshop' => $testWorkshop,
                'fundingsupporter' => $testFundingsupporter,
                'fundingdata' => $testFundingdata,
                'owner_user' => $testOwnerUser,
                'fundinguploads_activity_proofs' => [
                    $funding->fundinguploads_activity_proofs[0]->toArray(),
                ],
                'fundinguploads_freistellungsbescheids' => [
                    $funding->fundinguploads_freistellungsbescheids[0]->toArray(),
                ],
                'files_fundinguploads_activity_proofs' => [
                    new UploadedFile(
                        $uploadFileActivityProof2,
                        filesize($uploadFileActivityProof2),
                        UPLOAD_ERR_OK,
                        'test.txt',
                        'text/plain',
                    ),
                ],
                'files_fundinguploads_freistellungsbescheids' => [
                    new UploadedFile(
                        $uploadFileFreistellungsbescheid2,
                        filesize($uploadFileFreistellungsbescheid2),
                        UPLOAD_ERR_OK,
                        'test.jpg',
                        'image/jpeg',
                    ),
                ],
            ]
        ]);
        $this->assertResponseContains('Es ist nur eine Datei erlaubt.');
        $this->assertResponseContains('Nur PDF, JPG und PNG-Dateien sind erlaubt.');

        $funding = $fundingsTable->getUnprivatizedFundingWithAllAssociations($fundingUid);

        $this->assertCount(1, $funding->fundinguploads_activity_proofs);
        $this->assertCount(1, $funding->fundinguploads_freistellungsbescheids);

        // 2) POST test delete uploads
        $this->post(Configure::read('AppConfig.htmlHelper')->urlFundingsEdit($testWorkshopUid), [
            'referer' => '/',
            'Fundings' => [
                'workshop' => $testWorkshop,
                'fundingsupporter' => $testFundingsupporter,
                'fundingdata' => $testFundingdata,
                'owner_user' => $testOwnerUser,
                'delete_fundinguploads_freistellungsbescheids' => [
                    $funding->fundinguploads_freistellungsbescheids[0]->id,
                ],
                'delete_fundinguploads_activity_proofs' => [
                    $funding->fundinguploads_activity_proofs[0]->id,
                ],
            ]
        ]);

        $funding = $fundingsTable->getUnprivatizedFundingWithAllAssociations($fundingUid);
        $this->assertCount(0, $funding->fundinguploads_activity_proofs);
        $this->assertCount(0, $funding->fundinguploads_freistellungsbescheids);


        // cleanup everything including file uploads
        $fundingsTable = $this->getTableLocator()->get('Fundings');
        $fundingsTable->deleteCustom($funding->uid);

    }

    public function testIndex() {
        $testWorkshopUid = 2;
        $this->loginAsOrga();
        $this->prepareWorkshopForFunding($testWorkshopUid);
        $this->get(Configure::read('AppConfig.htmlHelper')->urlFundings());
        $this->assertResponseOk();
    }

    public function testDelete() {
        $this->loginAsOrga();
        $this->get(Configure::read('AppConfig.htmlHelper')->urlFundingsEdit(2));
        $fundingsTable = $this->getTableLocator()->get('Fundings');
        $funding = $fundingsTable->find()->where(['workshop_uid' => 2])->first();
        $this->get(Configure::read('AppConfig.htmlHelper')->urlFundingsDelete($funding->uid));
        $this->assertResponseCode(302);
        $this->assertRedirectContains(Configure::read('AppConfig.htmlHelper')->urlFundings());
        $this->assertFlashMessage('Der Förderantrag wurde erfolgreich gelöscht.');

        $deletedFunding = $fundingsTable->find()->where(['workshop_uid' => 2])->first();
        $this->assertEmpty($deletedFunding);

        $fundingsupportersTable = $this->getTableLocator()->get('Fundingsupporters');
        $fundingsupporter = $fundingsupportersTable->find()->where([$fundingsupportersTable->getPrimaryKey() => $funding->fundingsupporter_id])->first();
        $this->assertEmpty($fundingsupporter);

        $fundingdatasTable = $this->getTableLocator()->get('Fundingdatas');
        $fundingdata = $fundingdatasTable->find()->where([$fundingdatasTable->getPrimaryKey() => $funding->fundingdata_id])->first();
        $this->assertEmpty($fundingdata);

        $fundingbudgetplansTable = $this->getTableLocator()->get('Fundingbudgetplans');
        $fundingdatas = $fundingbudgetplansTable->find()->where([$fundingbudgetplansTable->aliasField('funding_uid') => $funding->uid])->toArray();
        $this->assertEmpty($fundingdatas);

        $fundinguploadsTable = $this->getTableLocator()->get('Fundinguploads');
        $fundinguploads = $fundinguploadsTable->find()->where([$fundinguploadsTable->aliasField('funding_uid') => $funding->uid])->toArray();
        $this->assertEmpty($fundinguploads);

    }

}
?>