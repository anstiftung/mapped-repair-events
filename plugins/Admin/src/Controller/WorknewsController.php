<?php
namespace Admin\Controller;

use App\Model\Table\WorknewsTable;

class WorknewsController extends AdminAppController
{

    public function index()
    {
        parent::index();

        $worknewsTable = $this->getTableLocator()->get('Worknews');
        $query = $worknewsTable->find('all',
        contain: [
            'Workshops',
        ]);
        $objects = $this->paginate($query, [
            'order' => [
                'Worknews.created' => 'DESC',
            ]
        ]);
        $this->set('objects', $objects);
    }
}
