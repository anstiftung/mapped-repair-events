<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class Provinces extends BaseMigration
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
        $this->table('provinces')
            ->addColumn('name', 'string', ['limit' => 255])
            ->addColumn('alternative_names', 'string', ['limit' => 1024])
            ->addColumn('country_code', 'string', ['limit' => 2])
            ->addColumn('created', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('modified', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->create();

        $this->table('users')
            ->addColumn('province_id', 'integer', ['null' => false, 'default' => 0, 'after' => 'lng'])
            ->update();

        $this->table('events')
        ->addColumn('province_id', 'integer', ['null' => false, 'default' => 0, 'after' => 'lng'])
            ->update();

        $this->table('workshops')
        ->addColumn('province_id', 'integer', ['null' => false, 'default' => 0, 'after' => 'lng'])
            ->update();

    }
}
