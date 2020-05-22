<?php
namespace App\Model\Table;

use Cake\ORM\Table;

class UsersWorkshopsTable extends Table
{

    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->setPrimaryKey([
            'user_uid',
            'workshop_uid'
        ]);
        $this->belongsTo('Users', [
            'foreignKey' => 'user_uid'
        ]);
        $this->belongsTo('Workshops', [
            'foreignKey' => 'workshop_uid'
        ]);
    }

    public function addApprovedUser($workshopUid, $userUid)
    {
        $query = 'REPLACE INTO ' . $this->getTable() . ' (user_uid, workshop_uid, created, approved) VALUES(:userUid, :workshopUid, NOW(), NOW());';
        $params = [
            'workshopUid' => $workshopUid,
            'userUid' => $userUid
        ];
        $this->getConnection()->execute($query, $params);
    }

}

?>