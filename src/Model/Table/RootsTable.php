<?php
declare(strict_types=1);

namespace App\Model\Table;

/**
 * @extends \App\Model\Table\AppTable<\App\Model\Entity\Root>
 */
class RootsTable extends AppTable
{

    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->setPrimaryKey('uid');
    }

    public function getType(int $uid): string
    {
        $data = $this->get($uid);
        return $data->object_type;
    }
}

?>