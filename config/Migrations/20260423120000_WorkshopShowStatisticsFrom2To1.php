<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class WorkshopShowStatisticsFrom2To1 extends BaseMigration
{
    public function change(): void
    {
        $query = "UPDATE `workshops` SET `show_statistics` = 1 WHERE `show_statistics` = 2";
        $this->execute($query);
    }
}
