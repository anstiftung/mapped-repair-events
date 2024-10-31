<?php
namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Routing\Router;

class FundingsTable extends Table
{

    public function initialize(array $config): void {
        parent::initialize($config);
        $this->belongsTo('Workshops', [
            'foreignKey' => 'workshop_uid'
        ]);
        $this->belongsTo('OwnerUsers', [
            'className' => 'Users',
            'foreignKey' => 'owner'
        ]);
    }


    public function findOrCreateCustom($workshopUid) {
        $funding = $this->findOrCreate([
            $this->aliasField('workshop_uid') => $workshopUid,
        ], function ($entity) use ($workshopUid) {

            $entity->workshop_uid = $workshopUid;
            $entity->status = APP_ON;
            $entity->owner = Router::getRequest()?->getAttribute('identity')?->uid;
        });

        $funding = $this->find()->where([
            $this->aliasField('workshop_uid') => $workshopUid,
        ])->contain([
            'Workshops',
        ])->first();

        return $funding;

    }

}

?>