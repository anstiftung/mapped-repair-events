<?php
declare(strict_types=1);
/**
 *    Root Model - Basisklasse fuer alle APP-Objekte s. app_object.php
 */
namespace App\Model\Table;

use Cake\Http\Exception\NotFoundException;
use Cake\ORM\Table;

class RootsTable extends Table
{

    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->setPrimaryKey('uid');
    }

    public function getType($uid)
    {
        $data = $this->get($uid);

        if (! $data) {
            throw new NotFoundException('Object not found! uid: ' . $uid);
        }

        return $data->object_type;
    }
}

?>