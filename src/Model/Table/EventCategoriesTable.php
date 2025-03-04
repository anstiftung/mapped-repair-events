<?php
declare(strict_types=1);

namespace App\Model\Table;

class EventCategoriesTable extends AppTable {
    public function initialize(array $config): void {
        parent::initialize($config);
        $this->setTable('events_categories');
        $this->setPrimaryKey('event_uid');
    }

}
