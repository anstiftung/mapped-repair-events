<?php
namespace Admin\Controller;

class QueuedJobsController extends AdminAppController
{

    public $searchName = false;
    public $searchText = false;
    public $searchUid = false;

    public function index()
    {
        parent::index();
        $queuedJobsTable = $this->getTableLocator()->get('QueuedJobs');
        
        $query = $queuedJobsTable->find();
        $objects = $this->paginate($query, ['order' => [
            $queuedJobsTable->aliasField('created') => 'DESC',
        ]]);
        $this->set('objects', $objects);

    }

}
?>