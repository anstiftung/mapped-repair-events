<?php
declare(strict_types=1);

namespace App\Test\TestCase\Queue\Task;

use App\Queue\Task\WorknewsEmailTask;
use App\Test\TestCase\AppTestCase;
use Cake\Mailer\Message;
use Cake\Mailer\Transport\DebugTransport;
use Cake\Mailer\TransportFactory;
use RuntimeException;

class WorknewsEmailTaskTest extends AppTestCase
{

    public function tearDown(): void
    {
        TransportFactory::drop('worknews-test');
        parent::tearDown();
    }

    public function testQuotaFailuresIncrementOnlyTheAffectedSubscription(): void
    {
        $messages = [
            'Mailbox is out of quota',
            '550 OUT-OF-QUOTA',
            '452 4.2.2 Recipient temporarily unavailable',
            '552 5.2.2 Recipient unavailable',
            '550 Mailbox full',
            '550 Mailbox is full',
            '552 Requested mail action aborted: exceeded storage allocation',
            '552 Storage allocation exceeded',
            '550 Disk quota exceeded',
            '550 Quota has been exceeded',
            '550 Quota is exceeded',
            '550 User has exceeded the quota',
            '550 User has exceeded mailbox quota',
            '550 User is over quota',
            '550 Over-quota',
            '550 Mailbox storage limit exceeded',
        ];
        $worknewsTable = $this->getTableLocator()->get('Worknews');
        foreach ($messages as $index => $message) {
            $transport = $this->createMock(DebugTransport::class);
            $exception = new RuntimeException($message);
            $transport->expects($this->once())->method('send')->willThrowException($exception);
            TransportFactory::drop('worknews-test');
            TransportFactory::setConfig('worknews-test', $transport);

            try {
                (new WorknewsEmailTask())->run($this->mailData(), 1);
                $this->fail('The delivery failure must be rethrown for queue retries.');
            } catch (RuntimeException $caught) {
                $this->assertSame($exception, $caught);
            }
            $this->assertSame($index + 1, $worknewsTable->get(1)->out_of_quota_count, $message);
        }

        $this->assertSame(0, $worknewsTable->get(2)->out_of_quota_count);
    }

    public function testOtherFailuresLeaveTheCountUnchanged(): void
    {
        $worknewsTable = $this->getTableLocator()->get('Worknews');
        $worknewsTable->updateAll(['out_of_quota_count' => 3], ['id' => 1]);
        $messages = [
            'Connection timed out',
            '550 5.1.1 User unknown',
            '552 5.3.4 Message size exceeds fixed maximum message size',
            '452 4.3.1 Insufficient system storage',
            '552 Message too large',
            '550 5.7.1 Sending quota exceeded',
        ];
        foreach ($messages as $message) {
            $transport = $this->createMock(DebugTransport::class);
            $exception = new RuntimeException($message);
            $transport->expects($this->once())->method('send')->willThrowException($exception);
            TransportFactory::drop('worknews-test');
            TransportFactory::setConfig('worknews-test', $transport);

            try {
                (new WorknewsEmailTask())->run($this->mailData(), 1);
                $this->fail('The delivery failure must be rethrown for queue retries.');
            } catch (RuntimeException $caught) {
                $this->assertSame($exception, $caught);
            }
            $this->assertSame(3, $worknewsTable->get(1)->out_of_quota_count, $message);
        }
    }

    public function testSuccessfulDeliveryResetsTheCount(): void
    {
        $worknewsTable = $this->getTableLocator()->get('Worknews');
        $worknewsTable->updateAll(['out_of_quota_count' => 3], ['id' => 1]);
        $transport = $this->createMock(DebugTransport::class);
        $transport->expects($this->once())->method('send')->willReturn([
            'headers' => '',
            'message' => '',
        ]);
        TransportFactory::setConfig('worknews-test', $transport);

        (new WorknewsEmailTask())->run($this->mailData(), 1);

        $this->assertSame(0, $worknewsTable->get(1)->out_of_quota_count);
    }

    /** @return array<string, mixed> */
    private function mailData(): array
    {
        $message = new Message();
        $message->setFrom('sender@example.org')->setTo('worknews-test@mailinator.com');

        return [
            'class' => Message::class,
            'settings' => $message->__serialize(),
            'serialized' => true,
            'transport' => 'worknews-test',
            'worknews_id' => 1,
        ];
    }

}