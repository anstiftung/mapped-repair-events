<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class Fundings extends AbstractMigration
{
    public function change(): void
    {
        $query = "CREATE TABLE `fundings` (
            `id` int UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `owner` int UNSIGNED DEFAULT NULL,
            `workshop_uid` int UNSIGNED DEFAULT NULL,
            `supporter_id` int UNSIGNED DEFAULT NULL,
            `status` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
            `activity_proof_filename` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
            `activity_proof_ok` tinyint(1) DEFAULT 0,
            `verified_fields` JSON DEFAULT NULL,
            `created` datetime DEFAULT CURRENT_TIMESTAMP,
            `modified` datetime DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        $this->execute($query);

        $query = "CREATE TABLE `supporters` (
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

    }
}
