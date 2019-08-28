<?php
namespace App\Controller;

use Cake\Event\Event;
use Cake\ORM\TableRegistry;
use Cake\Http\Exception\NotFoundException;

class SitemapsController extends AppController
{

    public function beforeFilter(Event $event)
    {
        parent::beforeFilter($event);
        $this->AppAuth->allow([
            'index'
        ]);
    }
    
    public function index()
    {
     
        if (!$this->RequestHandler->prefers('xml')) {
            throw new NotFoundException();
        }
        
        $this->RequestHandler->renderAs($this, 'xml');
        
        $this->Workshop = TableRegistry::getTableLocator()->get('Workshops');
        $workshops = $this->Workshop->find('all', [
            'conditions' => [
                'Workshops.status' => APP_ON
            ]
        ]);
        $this->set('workshops', $workshops);
        
        $this->Post = TableRegistry::getTableLocator()->get('Posts');
        $posts = $this->Post->find('all', [
            'conditions' => [
                'Posts.status' => APP_ON
            ]
        ]);
        $this->set('posts', $posts);
        
        $this->Page = TableRegistry::getTableLocator()->get('Pages');
        $pages = $this->Page->find('all', [
            'conditions' => [
                'Pages.status' => APP_ON
            ]
        ]);
        $this->set('pages', $pages);
        
    }

}
?>