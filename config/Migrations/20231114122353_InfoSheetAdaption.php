<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class InfoSheetAdaption extends BaseMigration
{

    public function change(): void
    {
        $this->execute("DELETE FROM `form_field_options` WHERE `form_field_options`.`id` = 6;");
        $this->execute("DELETE FROM `form_field_options` WHERE `form_field_options`.`id` = 7;");
        $this->execute("INSERT INTO `form_field_options` (`id`, `form_field_id`, `value`, `name`, `rank`, `status`) VALUES (NULL, '5', '15', 'Fehler nicht gefunden', '95', '1');");
        $this->execute("UPDATE `info_sheets` SET defect_found_reason = 3, no_repair_reason = 15 WHERE defect_found = 0;");
        $this->execute("DELETE FROM `form_field_options_extra_infos` WHERE `form_field_options_extra_infos`.`id` = 17;");
        $this->execute("INSERT INTO `form_field_options_extra_infos` (`id`, `form_field_options_id`, `repair_status`, `repair_barrier_if_end_of_life`) VALUES (NULL, '29', 'end of life', NULL);");
    }

}
