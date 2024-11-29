<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class AdaptQueueForAttachments extends AbstractMigration
{
    public function change(): void
    {
        $sql = "ALTER TABLE `queued_jobs` CHANGE `data` `data` MEDIUMTEXT CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL;";
        $this->execute($sql);
    }
}
