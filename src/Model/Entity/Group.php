<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

class Group extends Entity
{

    /**
     * Fields that are excluded from JSON versions of the entity.
     * @var array
     */
    protected $_hidden = [
        '_joinData',
        'UsersGroups'
    ];

}
