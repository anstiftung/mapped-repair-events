<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class Fundings3 extends AbstractMigration
{
    public function change(): void
    {
        $sql = "ALTER TABLE `fundings` ADD `submit_date` datetime NULL DEFAULT NULL AFTER `verified_fields`;";
        $this->execute($sql);
    }
}
