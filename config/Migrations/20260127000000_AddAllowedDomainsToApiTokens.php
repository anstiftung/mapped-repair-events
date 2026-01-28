<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddAllowedDomainsToApiTokens extends BaseMigration
{
    public function change(): void
    {
        $query = "ALTER TABLE `api_tokens` 
            ADD COLUMN `allowed_domains` JSON DEFAULT NULL AFTER `allowed_search_terms`";
        $this->execute($query);
    }
}
