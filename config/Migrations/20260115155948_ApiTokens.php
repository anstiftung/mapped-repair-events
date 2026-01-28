<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class ApiTokens extends BaseMigration
{
    public function change(): void
    {
        $query = "CREATE TABLE `api_tokens` (
            `id` int UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `name` varchar(255) NOT NULL,
            `token` varchar(64) NOT NULL,
            `allowed_search_terms` JSON DEFAULT NULL,
            `last_used` datetime DEFAULT NULL,
            `expires_at` datetime DEFAULT NULL,
            `status` tinyint(1) DEFAULT 1,
            `created` datetime DEFAULT CURRENT_TIMESTAMP,
            `modified` datetime DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY `token` (`token`),
            KEY `status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
        $this->execute($query);
    }
}
