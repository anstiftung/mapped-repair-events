<?php
declare(strict_types=1);

namespace App\Test\TestCase\Command;

use Cake\TestSuite\EmailTrait;
use App\Test\TestCase\AppTestCase;
use App\Test\TestCase\Traits\QueueTrait;
use Cake\Console\TestSuite\ConsoleIntegrationTestTrait;
use Cake\TestSuite\TestEmailTransport;

class ResendWorknewsActivationCommandTest extends AppTestCase
{

    use ConsoleIntegrationTestTrait;
    use EmailTrait;
    use QueueTrait;

    public function testRun(): void
    {
        $this->exec('resend_worknews_activation');
        $this->runAndAssertQueue();

        $this->assertMailCount(1);
        $this->assertMailSentToAt(0, 'worknews-test-1@mailinator.com');
        $this->assertMailContainsAt(0, 'Um dein Abonnement der Termine der Initiative <b>Test Workshop</b> zu aktivieren');
        $this->assertMailContainsAt(0, 'initiativen/newsact/07b9ec272178a9210f777beac7839a2f');

        $worknewsTable = $this->getTableLocator()->get('Worknews');
        $worknews = $worknewsTable->get(2);
        $this->assertNotNull($worknews->activation_email_resent);

        TestEmailTransport::clearMessages();
        $this->exec('resend_worknews_activation');
        $this->assertMailCount(0);

    }

}
