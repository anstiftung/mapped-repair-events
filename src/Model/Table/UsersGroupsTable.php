<?php
namespace App\Model\Table;

use Cake\ORM\Table;

class UsersGroupsTable extends Table
{

    public function initialize(array $config)
    {
        $this->belongsTo('Users');
        $this->belongsTo('Groups');
    }
}

?>