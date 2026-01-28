<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class Fundings extends BaseMigration
{
    public function change(): void
    {
        $query = "CREATE TABLE `fundings` (
            `_id` int UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `uid` int UNSIGNED DEFAULT NULL,
            `owner` int UNSIGNED DEFAULT NULL,
            `workshop_uid` int UNSIGNED DEFAULT NULL,
            `fundingsupporter_id` int UNSIGNED DEFAULT NULL,
            `fundingdata_id` int UNSIGNED DEFAULT NULL,
            `status` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
            `activity_proof_status` int(10) DEFAULT 10,
            `activity_proof_comment` text,
            `freistellungsbescheid_status` int(10) DEFAULT 10,
            `freistellungsbescheid_comment` text,
            `verified_fields` JSON DEFAULT NULL,
            `created` datetime DEFAULT CURRENT_TIMESTAMP,
            `modified` datetime DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        $this->execute($query);

        $query = "CREATE TABLE `fundingdatas` (
            `id` int UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `description` text,
            `created` datetime DEFAULT CURRENT_TIMESTAMP,
            `modified` datetime DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        $this->execute($query);

        $query = "CREATE TABLE `fundingsupporters` (
            `id` int UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
            `legal_form` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
            `zip` varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
            `city` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
            `street` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
            `addresszusatz` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
            `email` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
            `website` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
            `bank_account_owner` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
            `bank_institute` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
            `iban` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
            `contact_firstname` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
            `contact_lastname` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
            `contact_function` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
            `contact_phone` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
            `contact_email` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
            `created` datetime DEFAULT CURRENT_TIMESTAMP,
            `modified` datetime DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        $this->execute($query);

    $query = "CREATE TABLE `fundinguploads` (
            `id` CHAR(36) PRIMARY KEY,
            `funding_uid` int UNSIGNED DEFAULT NULL,
            `type` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
            `owner` int UNSIGNED DEFAULT NULL,
            `filename` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
            `created` datetime DEFAULT CURRENT_TIMESTAMP,
            `modified` datetime DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        $this->execute($query);

        $query = "CREATE TABLE `fundingbudgetplans` (
            `id` int UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `funding_uid` int UNSIGNED DEFAULT NULL,
            `type` int(10) DEFAULT 0,
            `description` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
            `amount` decimal(10,2) UNSIGNED NULL DEFAULT NULL,
            `created` datetime DEFAULT CURRENT_TIMESTAMP,
            `modified` datetime DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        $this->execute($query);

        $sql = "DELETE from roots where object_type = 'votings';
            DELETE from roots where object_type = 'coaches';";
        $this->execute($sql);

        $sql = "ALTER TABLE `roots` CHANGE `object_type` `object_type` ENUM('pages','users','posts','workshops','events','fundings','photos','info_sheets','knowledges') CHARACTER SET ascii COLLATE ascii_bin NULL DEFAULT NULL;";
        $this->execute($sql);

    }
}
