<?php
namespace App\Model\Entity;

use Cake\Auth\DefaultPasswordHasher;
use Cake\ORM\Entity;

class User extends Entity
{
    
    protected $_virtual = ['name'];
    public $privatize = true;
    
    public function __construct(array $properties = [], array $options = [])
    {
        parent::__construct($properties, $options);
        $this->privatizeData($this);
    }
    
    public function revertPrivatizeData()
    {
        foreach($this->extractOriginalChanged($this->getVisible()) as $property => $value) {
            $this->$property = $value; 
        }
    }
    
    public function privatizeData(&$user)
    {
        $privateFields = explode(',',  $user->private);
        $privateFields = str_replace('-', '_', $privateFields);
        foreach($user->getVisible() as $property) {
            if (in_array($property, $privateFields)) {
                $user->$property = null;
            }
        }
    }
    
    protected function _getName()
    {
        if (isset($this->_fields['firstname']) && isset($this->_fields['lastname'])) {
            return $this->_fields['firstname'] . ' ' . $this->_fields['lastname'];
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
     * @var array
     */
    protected $_hidden = [
        'password',
        '_joinData',
        'UsersWorkshops'
    ];
    
}
