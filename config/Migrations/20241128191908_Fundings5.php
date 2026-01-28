<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class Fundings5 extends BaseMigration
{
    public function change(): void
    {
        $sql = "ALTER TABLE `fundingsupporters` ADD bic varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL AFTER `iban`;";
        $this->execute($sql);
        $sql = "ALTER TABLE `fundings` CHANGE `status` `status` INT UNSIGNED NULL DEFAULT NULL;";
        $this->execute($sql);
    }
}
