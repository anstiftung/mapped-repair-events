<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddDefaultDefectDescription extends BaseMigration
{
    public function change(): void
    {
        $this->execute("ALTER TABLE `info_sheets` CHANGE `defect_description` `defect_description` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;");
    }
}
