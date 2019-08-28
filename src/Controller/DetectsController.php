<?php
namespace App\Controller;

use Cake\Event\Event;

class DetectsController extends AppController
{

    public function beforeFilter(Event $event)
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
            'viewChanged' => $this->Session->check('isMobile') && $this->Session->read('isMobile') != $isMobile,
            'isMobile' => $isMobile
        ]);
        $this->Session->write('isMobile', $isMobile);
        $this->set('_serialize', 'data');
        
    }

}
