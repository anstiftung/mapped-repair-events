<?php
namespace App\Controller;

use Cake\Event\EventInterface;
use Cake\View\JsonView;

class DetectsController extends AppController
{

    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);
        $this->Authentication->allowUnauthenticated([
            'setIsMobile'
        ]);
    }

    public function initialize(): void
    {
        parent::initialize();
        $this->addViewClasses([JsonView::class]);
    }

    public function setIsMobile()
    {
        $this->request = $this->request->withParam('_ext', 'json');
        $isMobile = $this->request->getData('width') < 768;
        $this->request->getSession()->write('isMobile', $isMobile);
        $this->set([
            'status' => 0,
            'width' => $this->request->getData('width'),
            'viewChanged' => $this->request->getSession()->check('isMobile') && $this->request->getSession()->read('isMobile') != $isMobile,
            'isMobile' => $isMobile
        ]);
        $this->viewBuilder()->setOption('serialize', ['status', 'message', 'width', 'isMobile']);
    }

}
