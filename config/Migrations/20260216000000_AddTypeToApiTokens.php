<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddTypeToApiTokens extends BaseMigration
{
    public function change(): void
    {
        $query = "ALTER TABLE `api_tokens`
            ADD COLUMN `type` int UNSIGNED NOT NULL DEFAULT 1 AFTER `token`";
        $this->execute($query);
    }
}
