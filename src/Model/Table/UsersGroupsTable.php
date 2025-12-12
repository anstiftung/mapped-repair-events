<?php
declare(strict_types=1);
namespace App\Model\Table;

/**
 * @extends \App\Model\Table\AppTable<\App\Model\Entity\UsersGroup>
 */
class UsersGroupsTable extends AppTable
{

    public function initialize(array $config): void
    {
        $this->belongsTo('Users');
        $this->belongsTo('Groups');
    }
}

?>