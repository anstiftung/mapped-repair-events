<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class Fundings4 extends BaseMigration
{
    public function change(): void
    {
        $sql = "ALTER TABLE `fundingdatas` DROP `checkbox_d`;";
        $this->execute($sql);
    }
}
