<?php
namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Utility\Text;

class FundinguploadsTable extends Table
{

    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->addBehavior('Timestamp');
        $this->belongsTo('Fundings', [
            'foreignKey' => 'funding_uid'
        ]);
    }

    public function beforeSave($event, $entity, $options)
    {
        if ($entity->isNew() && !$entity->id) {
            $entity->id = Text::uuid();
        }
    }

}

?>