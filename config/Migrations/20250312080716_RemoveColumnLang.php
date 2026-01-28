<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class RemoveColumnLang extends BaseMigration
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
