<?php
namespace App\Controller;

use Cake\Core\Configure;
use Cake\Event\EventInterface;
use Cake\Http\Exception\NotFoundException;

class PostsController extends AppController
{

    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);
        $this->Authentication->allowUnauthenticated([
            'detail',
            'getSplitter',
        ]);
    }

    public function getSplitter()
    {
        $this->RequestHandler->renderAs($this, 'json');

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

    public function detail()
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