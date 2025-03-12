<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class RemoveColumnLang extends AbstractMigration
{
    public function change(): void
    {
        $tables = ['workshops', 'events', 'pages', 'posts'];
        foreach ($tables as $table) {
            if ($this->table($table)->hasColumn('lang')) {
                $this->table($table)
                    ->removeColumn('lang')
                    ->update();
            }
        }
    }
}
