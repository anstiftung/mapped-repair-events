<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class RenameTable extends BaseMigration
{
    public function change(): void
    {
        $this->execute("RENAME TABLE form_field_options_extra_info TO form_field_options_extra_infos;");
    }
}
