<?php
/**
 *    Root Model - Basisklasse fuer alle APP-Objekte s. app_object.php
 */
namespace App\Model\Table;

use Cake\Http\Exception\NotFoundException;
use Cake\ORM\Table;

class RootsTable extends Table
{

    public function initialize(array $config)
    {
        parent::initialize($config);
        $this->setPrimaryKey('uid');
    }

    /**
     *
     * @param UID $uid
     * @return object_type bzw. Tabellen-Name wie 'destinations', 'coursedescriptions'
     */
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