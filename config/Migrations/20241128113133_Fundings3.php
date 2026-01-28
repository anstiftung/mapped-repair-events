<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class Fundings3 extends BaseMigration
{
    public function change(): void
    {
        $sql = "ALTER TABLE `fundings` ADD `submit_date` datetime NULL DEFAULT NULL AFTER `verified_fields`;";
        $this->execute($sql);
    }
}
