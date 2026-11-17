<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;

/**
 * @extends \Cake\ORM\Table<array<string, \Cake\ORM\Behavior>, \Cake\ORM\Entity>
 */
class EventSeriesTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->setTable('event_series');
        $this->setPrimaryKey('uid');
        $this->getSchema()->setColumnType('settings', 'json');
    }
}