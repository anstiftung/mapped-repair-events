<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class ResendWorknewsActivation extends BaseMigration
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
