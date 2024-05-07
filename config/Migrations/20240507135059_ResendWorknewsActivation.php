<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class ResendWorknewsActivation extends AbstractMigration
{
    public function change(): void
    {
        $this->table('worknews')
            ->addColumn('activation_email_resent', 'datetime', [
                'default' => null,
                'null' => true,
            ])
            ->update();
    }
}
