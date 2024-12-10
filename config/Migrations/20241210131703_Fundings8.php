<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class Fundings8 extends AbstractMigration
{
    public function change(): void
    {
        $sql = "ALTER TABLE `fundings` ADD `money_transfer_date` DATE NULL DEFAULT NULL AFTER `submit_date`;";
        $this->execute($sql);
    }
}
