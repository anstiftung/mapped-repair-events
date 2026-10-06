<?php
declare(strict_types=1);

namespace App\Queue\Task;

use Queue\Queue\Task\EmailTask;
use Throwable;

class WorknewsEmailTask extends EmailTask
{

    private const string QUOTA_ERROR_PATTERN = '/\A(?!.*\b(?:sending|sender)\s+quota\b).*(?:\b[45]\.2\.2\b'
        . '|\bout[ -]of[ -]quota\b'
        . '|\bover[ -]?quota\b'
        . '|\bmail[ -]?box(?:\s+is)?\s+full\b'
        . '|\bquota(?:\s+(?:is|has\s+been))?\s+exceeded\b'
        . '|\bexceeded\s+(?:(?:the|your)\s+)?(?:mailbox\s+)?quota\b'
        . '|\bexceeded\s+storage\s+allocation\b'
        . '|\bstorage\s+(?:allocation|limit)\s+exceeded\b)/is';

    public function run(array $data, int $jobId): void
    {
        try {
            parent::run($data, $jobId);
        } catch (Throwable $exception) {
            if (preg_match(self::QUOTA_ERROR_PATTERN, $exception->getMessage()) === 1) {
                $this->updateOutOfQuotaCount(true);
            }
            throw $exception;
        }

        $this->updateOutOfQuotaCount(false);
    }

    private function updateOutOfQuotaCount(bool $increment): void
    {
        if (!isset($this->message)) {
            return;
        }

        $errorsTable = $this->getTableLocator()->get('WorknewsEmailErrors');
        foreach (array_keys($this->message->getTo()) as $email) {
            if ($increment) {
                $errorsTable->incrementOutOfQuotaCount($email);
            } else {
                $errorsTable->resetOutOfQuotaCount($email);
            }
        }
    }

}