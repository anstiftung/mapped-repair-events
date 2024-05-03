<?php
namespace App\Controller;

use Cake\Event\EventInterface;
use Cake\Http\Exception\NotFoundException;
use Feed\View\RssView;
use Cake\Core\Configure;
use App\Model\Table\BlogsTable;
use App\Model\Table\PostsTable;

class BlogsController extends AppController
{

    public BlogsTable $Blog;
    public PostsTable $Post;

    public array $paginate = [
        'limit' => 10,
        'order' => [
            'Posts.publish' => 'DESC'
        ]
    ];

    public function initialize(): void
    {
        parent::initialize();
        $this->addViewClasses([RssView::class]);
    }

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

        $this->viewBuilder()->setClassName('Feed.Rss');
        
        $conditions = [
            'Posts.status' => APP_ON,
        ];

        // aktuelles blog should contain all blog posts (no blog id filter!)
        if (!empty($this->request->getParam('blogUrl'))) {
            $conditions['Blogs.url'] = $this->request->getParam('blogUrl');
        }

        $this->Post = $this->getTableLocator()->get('Posts');
        $posts = $this->Post->find('all',
        order: [
            'Posts.publish'=> 'DESC',
        ],
        conditions: $conditions,
        contain: [
            'Blogs',
            'Photos',
        ]);
        if ($posts->count() == 0) throw new NotFoundException('Kein RSS-Feeds gefunden');

        $data = [
            'channel' => [
                'title' => Configure::read('AppConfig.titleSuffix'),
                'description' => 'Neues von den Initiativen auf ' . Configure::read('AppConfig.titleSuffix'),
                'language' => 'de-DE',
            ],
            'items' => $this->preparePostsForFeed($posts),
        ];
        $this->set(['data' => $data]);
        $this->viewBuilder()->setOption('serialize', 'data');

    }

    private function preparePostsForFeed($posts)
    {
        $items = [];
        foreach ($posts as $post) {
            $link = Configure::read('AppConfig.htmlHelper')->urlPostDetail($post->url);
        
            $body = $post->publish->i18nFormat(Configure::read('DateFormat.de.DateLong2'));
            $body .= ' // ' . $post->name;
            $body .= '<br />' . h(strip_tags($post->text));
        
            $body = Configure::read('AppConfig.textHelper')->truncate($body, 400, [
                'ending' => '...',
                'exact'  => true,
                'html'   => true,
            ]);
        
            $preparedItem = [
                'title' => $post->name,
                'link' => $link,
                'guid' => ['url' => $link],
                'description' => $body,
                'pubDate' => $post->publish->i18nFormat(Configure::read('DateFormat.de.DateLong2'))
            ];
        
            if (!empty($post->photos)) {
                $imageUrl = Configure::read('AppConfig.htmlHelper')->getThumbs800ImageMultiple($post->photos[0]->name);
                $correctedImageUrl = str_replace('//', '/', WWW_ROOT . $imageUrl);
                $length = filesize($correctedImageUrl);
                $mimeType = mime_content_type($correctedImageUrl);
                $preparedItem['enclosure'] = ['url' => $imageUrl, 'length' => $length, 'type' => $mimeType];
            }
        
            $items[] = $preparedItem;
        }
        return $items;        
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
        $blog = $this->Blog->find('all', conditions: [
            'Blogs.url' => $url
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
        $query = $this->Post->find('all',
        conditions: [
            $conditions
        ],
        contain: [
            'Blogs'
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