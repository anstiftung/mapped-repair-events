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
                $this->updateOutOfQuotaCount((int)$data['worknews_id'], true);
            }
            throw $exception;
        }

        $this->updateOutOfQuotaCount((int)$data['worknews_id'], false);
    }

    private function updateOutOfQuotaCount(int $worknewsId, bool $increment): void
    {
        $query = $this->getTableLocator()->get('Worknews')->updateQuery();
        $query->set([
            'out_of_quota_count' => $increment ? $query->expr('out_of_quota_count + 1') : 0,
        ])
            ->where(['id' => $worknewsId])
            ->execute();
    }

}