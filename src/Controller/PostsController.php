<?php
declare(strict_types=1);
namespace App\Controller;

use App\Model\Table\PostsTable;
use Cake\Core\Configure;
use Cake\Event\EventInterface;
use Cake\Http\Exception\NotFoundException;
use Cake\View\JsonView;

class PostsController extends AppController
{

    public PostsTable $Post;
    
    public function beforeFilter(EventInterface $event): void
    {
        parent::beforeFilter($event);
        $this->Authentication->allowUnauthenticated([
            'detail',
            'getSplitter',
        ]);
    }

    public function initialize(): void
    {
        parent::initialize();
        $this->addViewClasses([JsonView::class]);
    }

    public function getSplitter(): void
    {
        $this->request = $this->request->withParam('_ext', 'json');
        $dir = new \DirectoryIterator(WWW_ROOT . Configure::read('AppConfig.splitterPath'));
        $prefix = 'SPLiTTER';
        $result = [];
        foreach ($dir as $fileinfo) {
            if (!$fileinfo->isDot()) {
                if (preg_match('`^'.$prefix.'(.*)\.pdf`', $fileinfo->getFilename())) {
                    $parts = explode('_', $fileinfo->getFilename());
                    $month = (int) substr($parts[1], 0, 2);
                    $year = str_replace($prefix, '', $parts[0]);
                    $name = $prefix . ' No. ' . $month . '/' . $year;
                    $result[] = [
                        'url' => Configure::read('AppConfig.serverName') . Configure::read('AppConfig.splitterPath') . '/' . $fileinfo->getFilename(),
                        'name' => $name,
                        'size' => Configure::read('AppConfig.numberHelper')->toReadableSize($fileinfo->getSize()),
                    ];
                }
            }
        }

        $this->set([
            'splitter' => $result,
        ]);

        $this->viewBuilder()->setOption('serialize', ['splitter']);

    }

    public function detail(): void
    {
        if (! isset($this->request->getParam('pass')['0'])) {
            throw new NotFoundException('page not found');
        }
        $url = $this->request->getParam('pass')['0'];

        if ($url == '')
            throw new NotFoundException('page not found');

        $this->Post = $this->getTableLocator()->get('Posts');
        $conditions = array_merge([
            'Posts.url' => $url,
            'Posts.status' => APP_ON
        ], $this->getPreviewConditions('Posts', $url));

        $post = $this->Post->find('all',
        conditions: $conditions,
        contain: [
            'Blogs',
            'Photos',
            'Metatags'
        ])->first();

        if (empty($post))
            throw new NotFoundException('post empty');

        $this->doPreviewChecks($post->status, Configure::read('AppConfig.htmlHelper')->urlPostDetail($post->url));

        $this->setContext($post);

        $this->set('post', $post);

        $metaTags = [
            'title' => $post->name,
            'keywords' => Configure::read('AppConfig.metaTags.' . $this->request->getParam('controller') . '.' . $this->request->getParam('action') . '.keywords'),
        ];
        $metaTags = $this->mergeCustomMetaTags($metaTags, $post);
        $this->set('metaTags', $metaTags);

    }
}
?>