<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class Fundings6 extends AbstractMigration
{
    public function change(): void
    {
        $sql = "ALTER TABLE `fundings`
            ADD `zuwendungsbestaetigung_status` int(10) DEFAULT 10 AFTER `freistellungsbescheid_comment`,
            ADD `zuwendungsbestaetigung_comment` text AFTER `zuwendungsbestaetigung_status`";
        $this->execute($sql);
    }
}
