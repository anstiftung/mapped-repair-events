<?php
declare(strict_types=1);

namespace App\Model\Table;

/**
 * @extends \App\Model\Table\AppTable<\App\Model\Entity\EventCategory>
 */
class EventCategoriesTable extends AppTable {
    public function initialize(array $config): void {
        parent::initialize($config);
        $this->setTable('events_categories');
        $this->setPrimaryKey('event_uid');
    }

}
