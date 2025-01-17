<?php
declare(strict_types=1);

namespace App\Test\TestCase\Command;

use Cake\TestSuite\EmailTrait;
use App\Test\TestCase\AppTestCase;
use App\Test\TestCase\Traits\QueueTrait;
use Cake\Console\TestSuite\ConsoleIntegrationTestTrait;

class SendWorknewsNotificationCommandTest extends AppTestCase
{

    use ConsoleIntegrationTestTrait;
    use EmailTrait;
    use QueueTrait;

    public function testRun(): void
    {
        $this->exec('send_worknews_notification');
        $this->runAndAssertQueue();
        $this->assertMailCount(1);
        $this->assertMailSentToAt(0, 'worknews-test@mailinator.com');
        $this->assertMailContainsAt(0, 'Die von dir abonnierte Initiative </b>Test Workshop</b> hat nächste Woche einen Termin:');
        $this->assertMailContainsAt(0, 'Veranstaltungsort: Berlin, Müllerstraße 123 (Haus Drei)');
    }

}
