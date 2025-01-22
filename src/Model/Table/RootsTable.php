<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;

class RootsTable extends Table
{

    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->setPrimaryKey('uid');
    }

    public function getType($uid): string
    {
        $data = $this->get($uid);
        return $data->object_type;
    }
}

?>