<?php
namespace App\Controller;

use Cake\Event\EventInterface;

class DetectsController extends AppController
{

    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);
        $this->AppAuth->allow([
            'setIsMobile'
        ]);
    }
    
    public function setIsMobile()
    {
        $this->RequestHandler->renderAs($this, 'json');
        $isMobile = $this->request->getData('width') < 768;
        $this->set('data', [
            'status' => 0,
            'width' => $this->request->getData('width'),
            'viewChanged' => $this->request->getSession()->check('isMobile') && $this->request->getSession()->read('isMobile') != $isMobile,
            'isMobile' => $isMobile
        ]);
        $this->request->getSession()->write('isMobile', $isMobile);
        $this->set('_serialize', 'data');
        
    }

}
