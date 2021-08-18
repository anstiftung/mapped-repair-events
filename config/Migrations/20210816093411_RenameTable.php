<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class RenameTable extends AbstractMigration
{
    public function change()
    {
        $this->execute("RENAME TABLE form_field_options_extra_info TO form_field_options_extra_infos;");
    }
}
