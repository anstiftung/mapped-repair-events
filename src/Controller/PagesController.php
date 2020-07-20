<?php
/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link      https://cakephp.org CakePHP(tm) Project
 * @since     0.2.9
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 */
namespace App\Controller;

use Cake\Core\Configure;
use Cake\Http\Cookie\Cookie;
use Cake\Http\Exception\NotFoundException;
use Cake\Event\EventInterface;
use DateTime;

/**
 * Static content controller
 *
 * This controller will render views from Template/Pages/
 *
 * @link https://book.cakephp.org/3.0/en/controllers/pages-controller.html
 */
class PagesController extends AppController
{

    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);
        $this->AppAuth->allow([
            'detail',
            'closeCovid19Banner'
        ]);
    }

    public function closeCovid19Banner() {
        $this->RequestHandler->renderAs($this, 'json');
        $cookie = (new Cookie('covid_19_banner'))
        ->withValue(
            [
                'show' => false,            ]
            )
            ->withExpiry(new DateTime('+7 day'));
            $this->setResponse($this->getResponse()->withCookie($cookie));

        $this->set([
            'status' => true
        ]);
        $this->viewBuilder()->setOption('serialize', ['status']);
    }

    public function detail()
    {

        if (! isset($this->request->getParam('pass')['0'])) {
            throw new NotFoundException('page not found');
        }
        $url = $this->request->getParam('pass')['0'];

        if ($url == '')
            throw new NotFoundException('page not found');

        $this->Page = $this->getTableLocator()->get('Pages');
        $conditions = array_merge([
            'Pages.url' => $url,
            'Pages.status' => APP_ON
        ], $this->getPreviewConditions('Pages', $url));

        $page = $this->Page->find('all', [
            'conditions' => $conditions,
            'contain' => [
                'Metatags'
            ]
        ])->first();

        if (empty($page))
            throw new NotFoundException('page empty');

        $this->doPreviewChecks($page->status, Configure::read('AppConfig.htmlHelper')->urlPageDetail($page->url));

        $this->setContext($page);

        $metaTags = [
            'title' => $page->name
        ];
        $metaTags = $this->mergeCustomMetaTags($metaTags, $page);
        $this->set('metaTags', $metaTags);

        $this->set('page', $page);

    }
}
