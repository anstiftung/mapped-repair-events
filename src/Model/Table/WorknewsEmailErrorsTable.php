<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;

/**
 * @extends \App\Model\Table\AppTable<\App\Model\Entity\WorknewsEmailError>
 */
class WorknewsEmailErrorsTable extends AppTable
{
    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->setPrimaryKey(['email', 'queued_job_id']);
    }

    public function incrementOutOfQuotaCount(string $email, int $jobId): void
    {
        $this->insertQuery()
            ->insert(['email', 'queued_job_id', 'out_of_quota_count'])
            ->values([
                'email' => $email,
                'queued_job_id' => $jobId,
                'out_of_quota_count' => 1,
            ])
            ->modifier('IGNORE')
            ->execute();
    }

    /**
     * @param \Cake\ORM\Query\SelectQuery<\App\Model\Entity\WorknewsEmailError> $query
     * @return \Cake\ORM\Query\SelectQuery<\App\Model\Entity\WorknewsEmailError>
     */
    public function findTotals(SelectQuery $query): SelectQuery
    {
        return $query->select([
            'email' => 'WorknewsEmailErrors.email',
            'out_of_quota_count' => $query->func()->sum('out_of_quota_count'),
        ])->groupBy(['email']);
    }

    public function getOutOfQuotaCount(string $email): int
    {
        $total = $this->find('totals')->where(['email' => $email])->first();

        return (int)($total->out_of_quota_count ?? 0);
    }

    public function resetOutOfQuotaCount(string $email): void
    {
        $this->updateAll(['out_of_quota_count' => 0], ['email' => $email]);
    }
}