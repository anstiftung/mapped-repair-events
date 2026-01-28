<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class Fundings8 extends BaseMigration
{
    public function change(): void
    {
        $sql = "ALTER TABLE `fundings` ADD `money_transfer_date` DATE NULL DEFAULT NULL AFTER `submit_date`;";
        $this->execute($sql);
    }
}
