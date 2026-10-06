<?php
declare(strict_types=1);

namespace App\Test\TestCase\Queue\Task;

use App\Queue\Task\WorknewsEmailTask;
use App\Test\TestCase\AppTestCase;
use Cake\Datasource\Paging\NumericPaginator;
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

    public function testQuotaFailuresAccumulateAcrossSubscriptionsWithTheSameEmail(): void
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
        $errorsTable = $this->getTableLocator()->get('WorknewsEmailErrors');
        foreach ($messages as $index => $message) {
            $transport = $this->createMock(DebugTransport::class);
            $exception = new RuntimeException($message);
            $transport->expects($this->once())->method('send')->willThrowException($exception);
            TransportFactory::drop('worknews-test');
            TransportFactory::setConfig('worknews-test', $transport);

            try {
                (new WorknewsEmailTask())->run($this->mailData($index % 2 === 0 ? 1 : 3), 1);
                $this->fail('The delivery failure must be rethrown for queue retries.');
            } catch (RuntimeException $caught) {
                $this->assertSame($exception, $caught);
            }
            $this->assertSame($index + 1, $errorsTable->get('worknews-test@mailinator.com')->out_of_quota_count, $message);
        }

        $this->assertSame(1, $errorsTable->find()->count());
    }

    public function testOtherFailuresLeaveTheCountUnchanged(): void
    {
        $errorsTable = $this->getTableLocator()->get('WorknewsEmailErrors');
        $errorsTable->saveOrFail($errorsTable->newEntity([
            'email' => 'worknews-test@mailinator.com',
            'out_of_quota_count' => 3,
        ]));
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
            $this->assertSame(3, $errorsTable->get('worknews-test@mailinator.com')->out_of_quota_count, $message);
        }
    }

    public function testSuccessfulDeliveryResetsTheCount(): void
    {
        $errorsTable = $this->getTableLocator()->get('WorknewsEmailErrors');
        $errorsTable->saveOrFail($errorsTable->newEntity([
            'email' => 'worknews-test@mailinator.com',
            'out_of_quota_count' => 3,
        ]));
        $errorsTable->saveOrFail($errorsTable->newEntity([
            'email' => 'worknews-test-1@mailinator.com',
            'out_of_quota_count' => 7,
        ]));
        $transport = $this->createMock(DebugTransport::class);
        $transport->expects($this->once())->method('send')->willReturn([
            'headers' => '',
            'message' => '',
        ]);
        TransportFactory::setConfig('worknews-test', $transport);

        (new WorknewsEmailTask())->run($this->mailData(3), 1);

        $this->assertSame(0, $errorsTable->get('worknews-test@mailinator.com')->out_of_quota_count);
        $this->assertSame(7, $errorsTable->get('worknews-test-1@mailinator.com')->out_of_quota_count);
    }

    public function testSuccessfulDeliveryWithoutErrorsDoesNotCreateACounter(): void
    {
        $transport = $this->createMock(DebugTransport::class);
        $transport->expects($this->once())->method('send')->willReturn([
            'headers' => '',
            'message' => '',
        ]);
        TransportFactory::setConfig('worknews-test', $transport);

        (new WorknewsEmailTask())->run($this->mailData(), 1);

        $this->assertSame(0, $this->getTableLocator()->get('WorknewsEmailErrors')->find()->count());
    }

    public function testCountsAreSharedAndSortableAcrossSubscriptions(): void
    {
        $errorsTable = $this->getTableLocator()->get('WorknewsEmailErrors');
        $errorsTable->incrementOutOfQuotaCount('worknews-test@mailinator.com');
        $errorsTable->incrementOutOfQuotaCount('worknews-test@mailinator.com');
        $worknewsTable = $this->getTableLocator()->get('Worknews');
        $query = $worknewsTable->find()->contain(['WorknewsEmailErrors']);
        $objects = (new NumericPaginator())->paginate($query, [
            'sort' => 'WorknewsEmailErrors.out_of_quota_count',
            'direction' => 'desc',
        ], [
            'sortableFields' => [
                ...$worknewsTable->getSchema()->columns(),
                'WorknewsEmailErrors.out_of_quota_count',
            ],
        ])->toArray();

        $this->assertCount(3, $objects);
        $this->assertSame('worknews-test@mailinator.com', $objects[0]->email);
        $this->assertSame('worknews-test@mailinator.com', $objects[1]->email);
        $this->assertSame(2, $objects[0]->worknews_email_error->out_of_quota_count);
        $this->assertSame(2, $objects[1]->worknews_email_error->out_of_quota_count);
        $this->assertSame('worknews-test-1@mailinator.com', $objects[2]->email);
        $this->assertNull($objects[2]->worknews_email_error);
    }

    public function testFailureUsesQueuedRecipientAfterSubscriptionIsDeleted(): void
    {
        $worknewsTable = $this->getTableLocator()->get('Worknews');
        $worknewsTable->deleteOrFail($worknewsTable->get(1));
        $transport = $this->createMock(DebugTransport::class);
        $exception = new RuntimeException('Mailbox is out of quota');
        $transport->expects($this->once())->method('send')->willThrowException($exception);
        TransportFactory::setConfig('worknews-test', $transport);

        try {
            (new WorknewsEmailTask())->run($this->mailData(), 1);
            $this->fail('The delivery failure must be rethrown for queue retries.');
        } catch (RuntimeException $caught) {
            $this->assertSame($exception, $caught);
        }

        $errorsTable = $this->getTableLocator()->get('WorknewsEmailErrors');
        $this->assertSame(1, $errorsTable->get('worknews-test@mailinator.com')->out_of_quota_count);
    }

    /** @return array<string, mixed> */
    private function mailData(int $worknewsId = 1): array
    {
        $message = new Message();
        $message->setFrom('sender@example.org')->setTo('worknews-test@mailinator.com');

        return [
            'class' => Message::class,
            'settings' => $message->__serialize(),
            'serialized' => true,
            'transport' => 'worknews-test',
            'worknews_id' => $worknewsId,
        ];
    }

}