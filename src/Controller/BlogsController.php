<?php
namespace App\Controller;

use Cake\Event\EventInterface;
use Cake\Http\Exception\NotFoundException;

class BlogsController extends AppController
{

    public array $paginate = [
        'limit' => 10,
        'order' => [
            'Posts.publish' => 'DESC'
        ]
    ];

    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);
        $this->Authentication->allowUnauthenticated([
            'detail',
            'feed'
        ]);
    }

    public function feed()
    {

        $this->request->addDetector(
            'rss',
            [
                'accept' => ['text/csv'],
                'param' => '_ext',
                'value' => 'rss',
            ]
        );

        if (! $this->request->is('rss')) {
            throw new NotFoundException('kein rss');
        }

        $conditions = array(
            'Posts.status' => APP_ON,
        );

        // aktuelles blog should contain all blog posts (no blog id filter!)
        if (!empty($this->request->getParam('blogUrl'))) {
            $conditions['Blogs.url'] = $this->request->getParam('blogUrl');
        }

        $this->Post = $this->getTableLocator()->get('Posts');
        $posts = $this->Post->find('all', array(
            'order' => [
                'Posts.publish'=> 'DESC'
            ],
            'conditions' => $conditions,
            'contain' => [
                'Blogs',
                'Photos',
            ]
        ));
        if ($posts->count() == 0) throw new NotFoundException('Kein RSS-Feeds gefunden');

        $this->set('posts', $posts);

    }

    public function detail()
    {
        if (empty($this->request->getParam('blogUrl'))) {
            throw new NotFoundException('page not found');
        }
        $url = $this->request->getParam('blogUrl');

        if ($url == '')
            throw new NotFoundException('page not found');

        $this->Blog = $this->getTableLocator()->get('Blogs');
        $blog = $this->Blog->find('all', [
            'conditions' => [
                'Blogs.url' => $url
            ]
        ])->first();

        if (empty($blog))
            throw new NotFoundException('blog empty');

        $this->set('blog', $blog);

        // get paginated posts
        $this->Post = $this->getTableLocator()->get('Posts');

        // aktuelles blog should contain all blog posts (no blog id filter!)
        $conditions = [
            'Posts.status' => APP_ON
        ];
        if ($blog->id > 1) {
            $conditions['Posts.blog_id'] = $blog->id;
        }
        $query = $this->Post->find('all', [
            'conditions' => [
                $conditions
            ],
            'contain' => [
                'Blogs'
            ]
        ]);
        $posts = $this->paginate($query, [
            'order' => [
                'Posts.publish' => 'DESC'
            ],
            'limit' => 1000
        ]);

        $urlOptions = [
            'url' => [
                'controller' => 'blog',
                'action' => $url
            ]
        ];
        $this->set('urlOptions', $urlOptions);

        $this->set('metaTags', ['title' => $blog->name]);
        $this->set('posts', $posts);
    }
}
?>