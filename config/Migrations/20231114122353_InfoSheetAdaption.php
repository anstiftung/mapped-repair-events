<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class InfoSheetAdaption extends AbstractMigration
{

    public function change(): void
    {
        $this->execute("INSERT INTO `form_field_options` (`id`, `form_field_id`, `value`, `name`, `rank`, `status`) VALUES (NULL, '3', '5', 'Fehler nicht gefunden', '50', '1');");
        $this->execute("UPDATE `info_sheets` SET defect_found_reason = 5 WHERE defect_found = 0;");
        $this->execute("DELETE FROM `form_field_options` WHERE `form_field_options`.`id` = 6;");
        $this->execute("DELETE FROM `form_field_options` WHERE `form_field_options`.`id` = 7;");
        $this->execute("DELETE FROM `form_field_options_extra_infos` WHERE `form_field_options_extra_infos`.`id` = 17;");
        $this->execute("INSERT INTO `form_field_options_extra_infos` (`id`, `form_field_options_id`, `repair_status`, `repair_barrier_if_end_of_life`) VALUES (NULL, '29', 'end of life', 'error not found');");
    }

}
