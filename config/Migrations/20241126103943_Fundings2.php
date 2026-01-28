<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class Fundings2 extends BaseMigration
{
    public function change(): void
    {
        $sql = "ALTER TABLE `fundingdatas` 
            ADD `checkbox_a` TINYINT UNSIGNED NOT NULL DEFAULT '0' AFTER `description`,
            ADD `checkbox_b` TINYINT UNSIGNED NOT NULL DEFAULT '0' AFTER `checkbox_a`,
            ADD `checkbox_c` TINYINT UNSIGNED NOT NULL DEFAULT '0' AFTER `checkbox_b`,
            ADD `checkbox_d` TINYINT UNSIGNED NOT NULL DEFAULT '0' AFTER `checkbox_c`;
        ";
        $this->execute($sql);
    }
}
