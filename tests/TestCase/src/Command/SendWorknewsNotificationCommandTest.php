<?php
declare(strict_types=1);

namespace App\Test\TestCase\Command;

use Cake\TestSuite\EmailTrait;
use App\Test\TestCase\AppTestCase;
use Cake\Console\TestSuite\ConsoleIntegrationTestTrait;

class SendWorknewsNotificationCommandTest extends AppTestCase
{

    use ConsoleIntegrationTestTrait;
    use EmailTrait;

    public function testRun()
    {
        $this->exec('send_worknews_notification');
        $this->assertMailCount(1);
        $this->assertMailSentToAt(0, 'worknews-test@mailinator.com');
        $this->assertMailContainsAt(0, 'Die von dir abonnierte Initiative </b>Test Workshop</b> hat nächste Woche einen Termin:');
        $this->assertMailContainsAt(0, 'Veranstaltungsort: Berlin, Müllerstraße 123 (Haus Drei)');
    }

}
