<?php
namespace App\Model\Entity;

use Cake\Auth\DefaultPasswordHasher;
use Cake\ORM\Entity;

class User extends Entity
{
    
    protected $_virtual = ['name', 'MappedRepairEvents_fask'];
    
    protected function _getName()
    {
        if (isset($this->_properties['firstname']) && isset($this->_properties['lastname'])) {
            return $this->_properties['firstname'] . ' ' . $this->_properties['lastname'];
        }
        return '';
    }
    
    protected function _setPassword($password)
    {
        if (strlen($password) > 0) {
            return (new DefaultPasswordHasher)->hash($password);
        }
    }

    /**
     * Fields that are excluded from JSON versions of the entity.
     *
     * @var array
     */
    protected $_hidden = [
        'password'
    ];
}
