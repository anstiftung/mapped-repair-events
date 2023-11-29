<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class InfoSheetAdaption2 extends AbstractMigration
{
    public function change(): void
    {
        $this->execute("ALTER TABLE `info_sheets` DROP `defect_found`;");
    }
}
