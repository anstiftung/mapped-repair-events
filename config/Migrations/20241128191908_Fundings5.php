<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class Fundings5 extends AbstractMigration
{
    public function change(): void
    {
        $sql = "ALTER TABLE `fundingsupporters` ADD bic varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL AFTER `iban`;";
        $this->execute($sql);
    }
}
