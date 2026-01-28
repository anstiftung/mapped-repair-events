<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class RemoveNewsletter extends BaseMigration
{
    public function change(): void
    {
        $this->execute("DROP table newsletters;");
    }
}
