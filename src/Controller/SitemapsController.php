<?php
declare(strict_types=1);
namespace App\Controller;

use App\Model\Table\PagesTable;
use App\Model\Table\PostsTable;
use App\Model\Table\WorkshopsTable;
use Cake\Event\EventInterface;
use Cake\View\XmlView;

class SitemapsController extends AppController
{

    public WorkshopsTable $Workshop;
    public PostsTable $Post;
    public PagesTable $Page;
    
    public function beforeFilter(EventInterface $event): void
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

    public function index(): void
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