<?php
declare(strict_types=1);

use Migrations\AbstractMigration;
use App\Model\Entity\Funding;

class Verwendungsnachweis1 extends AbstractMigration
{
    public function change(): void
    {

        $sql = "ALTER TABLE `fundings` ADD `usageproof_status` int(10) DEFAULT " . Funding::STATUS_DATA_MISSING . " AFTER `zuwendungsbestaetigung_comment`,
                ADD `usageproof_comment` text AFTER `usageproof_status`,
                ADD `fundingusageproof_id` int UNSIGNED DEFAULT NULL AFTER `fundingdata_id`;
        ";
        $this->execute($sql);

        $query = "CREATE TABLE `fundingusageproofs` (
            `id` int UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `main_description` text,
            `sub_description` text,
            `created` datetime DEFAULT CURRENT_TIMESTAMP,
            `modified` datetime DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        $this->execute($query);

        $query = "CREATE TABLE `fundingreceiptlists` (
            `id` int UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `funding_uid` int UNSIGNED DEFAULT NULL,
            `type` int(10) DEFAULT 0,
            `description` varchar(150) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
            `amount` decimal(10,2) UNSIGNED NULL DEFAULT NULL,
            `created` datetime DEFAULT CURRENT_TIMESTAMP,
            `modified` datetime DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        $this->execute($query);

    }
}
