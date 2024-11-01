<?php
namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Routing\Router;
use Cake\Database\Schema\TableSchemaInterface;

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

    public function getSchema(): TableSchemaInterface
    {
        return parent::getSchema()->setColumnType('verified_fields', 'json');
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
            $this->aliasField('owner') => Router::getRequest()?->getAttribute('identity')?->uid,
        ])->contain([
            'Workshops.Countries',
            'OwnerUsers.Countries',
        ])->first();

        if (!empty($funding)) {
            $funding->owner_user->revertPrivatizeData();
        }
        return $funding;
    }

}

?>