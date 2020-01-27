<?php
namespace App\Model\Table;

use Cake\ORM\Table;

class UsersCategoriesTable extends Table
{
    
    public function initialize(array $config): void
    {
        $this->belongsTo('Users');
        $this->belongsTo('Categories');
    }
}

?>