<?php
declare(strict_types=1);
namespace App\Model\Table;

use Cake\ORM\Table;

class UsersGroupsTable extends Table
{

    public function initialize(array $config): void
    {
        $this->belongsTo('Users');
        $this->belongsTo('Groups');
    }
}

?>