<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class WorknewsEmailErrors extends BaseMigration
{
    public function change(): void
    {
        $this->table('worknews_email_errors', [
            'id' => false,
            'primary_key' => ['email', 'queued_job_id'],
        ])
            ->addColumn('email', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('queued_job_id', 'integer', ['default' => 0, 'null' => false])
            ->addColumn('out_of_quota_count', 'integer', ['default' => 0, 'null' => false])
            ->create();
    }
}