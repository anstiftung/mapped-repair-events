<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class ChangeZeroDatesForMySql8 extends AbstractMigration
{

    public function change(): void
    {

        $tables = [
            'brands' => ['created', 'modified'],
            'categories' => ['created', 'modified'],
            'events' => ['created', 'updated', 'currently_updated_start'],
            'info_sheets' => ['created', 'updated', 'currently_updated_start'],
            'ords_categories' => ['created', 'modified'],
            'pages' => ['created', 'updated', 'currently_updated_start'],
            'photos' => ['created', 'updated', 'currently_updated_start'],
            'posts' => ['created', 'updated', 'currently_updated_start'],
            'skills' => ['created', 'modified'],
            'users' => ['created', 'updated', 'currently_updated_start'],
            'users_workshops' => ['created', 'approved'],
            'worknews' => ['created', 'modified'],
            'workshops' => ['created', 'updated', 'currently_updated_start'],
        ];
        foreach($tables as $table => $fields) {
            foreach($fields as $field) {
                $sql = 'UPDATE '.$table . ' SET ' . $field . ' = "1970-01-01 00:00:00" WHERE ' . $field . ' = "0000-00-00 00:00:00"';
                echo $sql . "\n";
                $this->execute($sql);
            }
        }

        $sql = 'UPDATE events SET datumstart = "1970-01-01" WHERE datumstart = "0000-00-00"';
        $this->execute($sql);

    }

}
