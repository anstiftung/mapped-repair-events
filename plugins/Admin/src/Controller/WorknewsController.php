<?php
declare(strict_types=1);
namespace Admin\Controller;

use Cake\Event\EventInterface;

class WorknewsController extends AdminAppController
{

    public function beforeFilter(EventInterface $event): void
    {
        $this->searchUid = false;
        $this->searchName = false;
        $this->searchText = false;
        $this->searchStatus = false;

        $this->addSearchOptions([
            'Worknews.email' => [
                'name' => 'Worknews.email',
                'searchType' => 'search'
            ],
            'Worknews.workshop_uid' => [
                'name' => 'Worknews.workshop_uid',
                'searchType' => 'search'
            ],
            'Worknews.confirm' => [
                'name' => 'Worknews.confirm',
                'searchType' => 'search',
                'negate' => true,
            ],
        ]);

        parent::beforeFilter($event);

    }

    public function index(): void
    {
        parent::index();

        $conditions = [];
        $conditions = array_merge($this->conditions, $conditions);

        $worknewsTable = $this->getTableLocator()->get('Worknews');
        $query = $worknewsTable->find('all',#
        conditions: $conditions,
        contain: [
            'Workshops',
        ]);
        $objects = $this->paginate($query, [
            'order' => [
                'Worknews.created' => 'DESC',
            ]
        ]);
        foreach($objects as $object) {
            $object->worknews_count = $worknewsTable->getSubscribers($object->workshop_uid)->count();
        }
        $this->set('objects', $objects);
    }
}
