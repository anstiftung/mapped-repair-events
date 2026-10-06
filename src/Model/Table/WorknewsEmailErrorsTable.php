<?php
declare(strict_types=1);

namespace App\Model\Table;

/**
 * @extends \App\Model\Table\AppTable<\App\Model\Entity\WorknewsEmailError>
 */
class WorknewsEmailErrorsTable extends AppTable
{
    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->setPrimaryKey('email');
    }

    public function incrementOutOfQuotaCount(string $email): void
    {
        $this->getConnection()->execute(
            'INSERT INTO worknews_email_errors (email, out_of_quota_count) VALUES (:email, 1)
                ON DUPLICATE KEY UPDATE out_of_quota_count = out_of_quota_count + 1',
            ['email' => $email],
            ['email' => 'string'],
        );
    }

    public function resetOutOfQuotaCount(string $email): void
    {
        $this->updateAll(['out_of_quota_count' => 0], ['email' => $email]);
    }
}