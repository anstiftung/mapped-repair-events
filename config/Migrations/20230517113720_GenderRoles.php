<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class GenderRoles extends BaseMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     * @return void
     */
    public function change(): void
    {

        $tables = ['pages', 'posts'];
        foreach($tables as $table) {
            $this->execute("UPDATE $table SET text = REPLACE(text, 'OrganisatorIn', 'Organisator*in');");
            $this->execute("UPDATE $table SET text = REPLACE(text, 'Organisator', 'Organisator*in');");
            $this->execute("UPDATE $table SET text = REPLACE(text, 'ReparaturhelferIn', 'Reparaturhelfer*in');");
            $this->execute("UPDATE $table SET text = REPLACE(text, 'Reparaturhelfer', 'Reparaturhelfer*in');");
        }
    }

}
