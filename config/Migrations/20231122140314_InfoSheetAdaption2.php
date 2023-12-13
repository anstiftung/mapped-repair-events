<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class InfoSheetAdaption2 extends AbstractMigration
{
    public function change(): void
    {
        $this->execute("ALTER TABLE `info_sheets` DROP `defect_found`;");

        $this->execute("ALTER TABLE `workshops` DROP INDEX `text`;
            ALTER TABLE `events` ADD INDEX(`datumstart`);
            ALTER TABLE `events` ADD INDEX(`uhrzeitstart`);
            ALTER TABLE `events` ADD INDEX(`workshop_uid`);
            ALTER TABLE `events` ADD INDEX(`ort`);
            ALTER TABLE `events` ADD INDEX(`zip`);
            ALTER TABLE `info_sheets` ADD INDEX(`status`);
            ALTER TABLE `info_sheets` ADD INDEX(`defect_found_reason`);
            ALTER TABLE `info_sheets` ADD INDEX(`repair_postponed_reason`);
            ALTER TABLE `info_sheets` ADD INDEX(`no_repair_reason`);
            ALTER TABLE `info_sheets` ADD INDEX(`event_uid`);"
        );

    }
}
