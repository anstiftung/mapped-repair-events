<?php
declare(strict_types=1);
namespace App\Model\Table;

use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\ORM\Table;
use Cake\Utility\Text;
use ArrayObject;

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

    /**
     * @param \Cake\Event\EventInterface<\Cake\Datasource\EntityInterface> $event
     * @param \ArrayObject<string, mixed> $options
     */
    public function beforeSave(EventInterface $event, EntityInterface $entity, ArrayObject $options): void
    {
        if ($entity->isNew() && !$entity->id) {
            $entity->id = Text::uuid();
        }
    }

}

?>