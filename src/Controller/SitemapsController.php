<?php
namespace App\Controller;

use Cake\Event\EventInterface;
use Cake\Http\Exception\NotFoundException;
use Cake\View\XmlView;

class SitemapsController extends AppController
{

    public $Workshop;
    public $Post;
    public $Page;
    
    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);
        $this->Authentication->allowUnauthenticated([
            'index'
        ]);
    }

    public function initialize(): void
    {
        parent::initialize();
        $this->addViewClasses([XmlView::class]);
    }

    public function index()
    {

        $this->request = $this->request->withParam('_ext', 'xml');

        $this->Workshop = $this->getTableLocator()->get('Workshops');
        $workshops = $this->Workshop->find('all', conditions: [
            'Workshops.status' => APP_ON
        ]);
        $this->set('workshops', $workshops);

        $this->Post = $this->getTableLocator()->get('Posts');
        $posts = $this->Post->find('all', conditions: [
            'Posts.status' => APP_ON
        ]);
        $this->set('posts', $posts);

        $this->Page = $this->getTableLocator()->get('Pages');
        $pages = $this->Page->find('all', conditions: [
            'Pages.status' => APP_ON
        ]);
        $this->set('pages', $pages);

    }

}
?>