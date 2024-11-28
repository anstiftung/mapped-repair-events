<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class Fundings4 extends AbstractMigration
{
    public function change(): void
    {
        $sql = "ALTER TABLE `fundingdatas` DROP `checkbox_d`;";
        $this->execute($sql);
    }
}
