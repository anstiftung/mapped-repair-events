<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class OnlineEvent extends BaseMigration
{
    public function change(): void
    {
        $this->execute("ALTER TABLE `events` ADD `is_online_event` TINYINT(4) UNSIGNED NOT NULL DEFAULT '0' AFTER `workshop_uid`;");
    }
}
