<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class WorknewsOutOfQuotaCount extends BaseMigration
{
    public function change(): void
    {
        $this->table('worknews')
            ->addColumn('out_of_quota_count', 'integer', [
                'default' => 0,
                'null' => false,
            ])
            ->update();
    }
}