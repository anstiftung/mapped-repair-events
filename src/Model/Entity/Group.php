<?php
declare(strict_types=1);
namespace App\Model\Entity;

use Cake\ORM\Entity;

class Group extends Entity
{

    protected array $_hidden = [
        '_joinData',
        'UsersGroups'
    ];

}
