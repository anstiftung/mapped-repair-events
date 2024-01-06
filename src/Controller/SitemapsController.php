<?php
namespace App\Controller;

use Cake\Event\EventInterface;
use Cake\Http\Exception\NotFoundException;

class SitemapsController extends AppController
{

    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);
        $this->Authentication->allowUnauthenticated([
            'index'
        ]);
    }

    public function index()
    {

        if (!$this->RequestHandler->prefers('xml')) {
            throw new NotFoundException();
        }

        $this->RequestHandler->renderAs($this, 'xml');

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