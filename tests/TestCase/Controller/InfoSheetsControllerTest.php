<?php

namespace App\Test\TestCase\Controller;

use App\Test\TestCase\Traits\LoadAllFixturesTrait;
use App\Test\TestCase\Traits\LogFileAssertionsTrait;
use App\Test\TestCase\Traits\LoginTrait;
use App\Test\TestCase\Traits\UserAssertionsTrait;
use Cake\Core\Configure;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\StringCompareTrait;
use Cake\TestSuite\TestCase;

class InfoSheetsControllerTest extends TestCase
{
    use LoginTrait;
    use IntegrationTestTrait;
    use UserAssertionsTrait;
    use StringCompareTrait;
    use LogFileAssertionsTrait;
    use LoadAllFixturesTrait;
    
    public function testAddInfoSheet()
    {
        $this->markTestSkipped();
        $this->loginAsOrga();
        $this->get(Configure::read('AppConfig.htmlHelper')->urlInfoSheetNew(6));
    }
    
}
?>