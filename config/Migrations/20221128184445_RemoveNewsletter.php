<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class RemoveNewsletter extends AbstractMigration
{
    public function change()
    {
        $this->execute("DROP table newsletters;");
    }
}
