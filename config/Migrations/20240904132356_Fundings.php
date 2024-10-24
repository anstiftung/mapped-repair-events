<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class Fundings extends AbstractMigration
{
    public function change(): void
    {
        $query = "CREATE TABLE `fundings` (
            `id` int UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `user_uid` int UNSIGNED DEFAULT NULL,
            `workshop_uid` int UNSIGNED DEFAULT NULL,
            `status` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
            `created` datetime DEFAULT CURRENT_TIMESTAMP,
            `modified` datetime DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        $this->execute($query);

    }
}