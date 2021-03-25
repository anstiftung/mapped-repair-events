<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class OnlineEvent extends AbstractMigration
{
    public function change()
    {
        $this->execute("ALTER TABLE `events` ADD `is_online_event` TINYINT(4) UNSIGNED NOT NULL DEFAULT '0' AFTER `workshop_uid`;");
    }
}
