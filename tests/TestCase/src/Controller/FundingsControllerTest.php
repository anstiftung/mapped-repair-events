<?php

namespace App\Test\TestCase\Controller;

use App\Test\Fixture\WorkshopsFixture;
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

    public function testRoutesLoggedOut() {
        $this->get(Configure::read('AppConfig.htmlHelper')->urlFunding());
        $this->assertResponseCode(302);
        $this->get(Configure::read('AppConfig.htmlHelper')->urlFundingDetail(2));
        $this->assertResponseCode(302);
    }

    public function testRoutesAsRepairhelper() {
        $this->loginAsRepairhelper();
        $this->get(Configure::read('AppConfig.htmlHelper')->urlFunding());
        $this->assertResponseCode(302);
        $this->get(Configure::read('AppConfig.htmlHelper')->urlFundingDetail(2));
        $this->assertResponseCode(302);
    }

    public function testDetailOk() {
        $this->loginAsOrga();
        $this->get(Configure::read('AppConfig.htmlHelper')->urlFundingDetail(2));
        $this->assertResponseOk();
    }

    public function testDetailNotOk() {
        $this->loginAsOrga();
        $workshopsTable = $this->getTableLocator()->get('Workshops');
        $workshop = $workshopsTable->get(2);
        $workshop->country_code = 'AT';
        $workshopsTable->save($workshop);
        $this->get(Configure::read('AppConfig.htmlHelper')->urlFundingDetail(2));
        $this->assertResponseCode(302);
        $this->assertRedirectContains(Configure::read('AppConfig.htmlHelper')->urlFunding());
    }

}
?>