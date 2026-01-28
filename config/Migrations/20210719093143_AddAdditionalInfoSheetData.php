<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddAdditionalInfoSheetData extends BaseMigration
{
    public function change(): void
    {

        $this->execute("
            INSERT INTO `form_field_options` (`id`, `form_field_id`, `value`, `name`, `rank`, `status`) VALUES
            (25, 5, 11, 'Öffnung des Gerätes nicht möglich', 25, 1),
            (26, 5, 12, 'Ersatzteil zu teuer', 45, 1),
            (27, 5, 13, 'Reparaturinformationen nicht verfügbar', 47, 1),
            (28, 5, 14, 'Abnutzung zu groß', 55, 1);
        ");

        $this->execute("
            CREATE TABLE `form_field_options_extra_info` (
              `id` int(11) NOT NULL,
              `form_field_options_id` int(10) UNSIGNED NOT NULL,
              `repair_status` varchar(40) DEFAULT NULL,
              `repair_barrier_if_end_of_life` varchar(200) DEFAULT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

            INSERT INTO `form_field_options_extra_info` (`id`, `form_field_options_id`, `repair_status`, `repair_barrier_if_end_of_life`) VALUES
            (1, 11, 'fixed', NULL),
            (2, 8, 'repairable', NULL),
            (3, 9, 'repairable', NULL),
            (4, 10, 'repairable', NULL),
            (5, 15, 'repairable', NULL),
            (6, 16, 'end of life', 'lack of equipment'),
            (7, 17, 'end of life', NULL),
            (8, 18, 'end of life', 'spare parts not available'),
            (9, 19, 'repairable', NULL),
            (10, 20, 'end of life', NULL),
            (11, 23, 'repairable', NULL),
            (12, 24, 'unknown', NULL),
            (13, 25, 'end of life', 'no way to open the product'),
            (14, 26, 'end of life', 'spare parts too expensive'),
            (15, 27, 'end of life', 'repair information not available'),
            (16, 28, 'end of life', 'product too worn out'),
            (17, 7, 'unknown', NULL);
            ALTER TABLE `form_field_options_extra_info`
              ADD PRIMARY KEY (`id`);
            ALTER TABLE `form_field_options_extra_info`
              MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;
            COMMIT;
        ");

    }
}
