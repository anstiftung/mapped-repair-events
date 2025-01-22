<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class AddDefaultDefectDescription extends AbstractMigration
{
    public function change(): void
    {
        $this->execute("ALTER TABLE `info_sheets` CHANGE `defect_description` `defect_description` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;");
    }
}
