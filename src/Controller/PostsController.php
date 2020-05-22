<?php
namespace App\Controller;

use Cake\Core\Configure;
use Cake\Event\EventInterface;
use Cake\Http\Exception\NotFoundException;
use Cake\ORM\TableRegistry;

class PostsController extends AppController
{

    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);
        $this->AppAuth->allow([
            'detail'
        ]);
    }

    public function detail()
    {
        if (! isset($this->request->getParam('pass')['0'])) {
            throw new NotFoundException('page not found');
        }
        $url = $this->request->getParam('pass')['0'];

        if ($url == '')
            throw new NotFoundException('page not found');

        $this->Post = TableRegistry::getTableLocator()->get('Posts');
        $conditions = array_merge([
            'Posts.url' => $url,
            'Posts.status' => APP_ON
        ], $this->getPreviewConditions('Posts', $url));

        $post = $this->Post->find('all', [
            'conditions' => $conditions,
            'contain' => [
                'Blogs',
                'Photos',
                'Metatags'
            ]
        ])->first();

        if (empty($post))
            throw new NotFoundException('post empty');

        $this->doPreviewChecks($post->status, Configure::read('AppConfig.htmlHelper')->urlPostDetail($post->url));

        $this->setContext($post);

        $this->set('post', $post);

        $metaTags = [
            'title' => $post->name,
            'keywords' => 'netzwerk reparatur-initiativen, reparatur-initiativen, anstiftung, repair café, repair-café, repair, reparatur, repaircafé, reparieren, reparatur café'
        ];
        $metaTags = $this->mergeCustomMetaTags($metaTags, $post);
        $this->set('metaTags', $metaTags);

    }
}
?>