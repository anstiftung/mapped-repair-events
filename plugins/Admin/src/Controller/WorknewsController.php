<?php
namespace Admin\Controller;

use App\Model\Table\WorknewsTable;

class WorknewsController extends AdminAppController
{

    public WorknewsTable $Worknews;
    
    public function __construct($request = null, $response = null)
    {
        parent::__construct($request, $response);
        $this->Worknews = $this->getTableLocator()->get('Worknews');
    }

    public function index()
    {
        parent::index();

        $query = $this->Worknews->find('all',
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
