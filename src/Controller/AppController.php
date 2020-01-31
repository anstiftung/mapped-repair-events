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

use Cake\Cache\Cache;
use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use Cake\Event\EventInterface;
use Cake\I18n\Time;
use Cake\Http\Exception\NotFoundException;
use Cake\Http\Exception\ServiceUnavailableException;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;
use Cake\Utility\Inflector;
use Mobile_Detect;

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @link https://book.cakephp.org/3.0/en/controllers.html#the-app-controller
 */
class AppController extends Controller
{

    public $useDefaultValidation = true;
    
    public $modelName;
    
    public function __construct($request = null, $response = null)
    {
        parent::__construct($request, $response);
        $this->connection = ConnectionManager::get('default');
        $this->Root = TableRegistry::getTableLocator()->get('Roots');
        $this->modelName = Inflector::classify($this->name);
        $this->pluralizedModelName = Inflector::pluralize($this->modelName);
    }
    
    
    /**
     * Initialization hook method.
     *
     * Use this method to add common initialization code like loading components.
     *
     * e.g. `$this->loadComponent('Security');`
     *
     * @return void
     */
    public function initialize(): void
    {
        
        parent::initialize();
        
        $this->loadComponent('Common');
        $this->loadComponent('String');
        $this->loadComponent('RequestHandler', [
            'enableBeforeRedirect' => false
        ]);
        if (Configure::read('AppConfig.fluxBbForumEnabled')) {
            $this->loadComponent('FluxBb');
        }
        $this->loadComponent('AppFlash', [
            'clear' => true
        ]);
        
        $this->loadComponent('AppAuth', [
            'logoutRedirect' => '/',
            'authError' => 'Zugriff verweigert, bitte melde dich an.',
            'authorize' => [
                'Controller'
            ],
            'loginError' => 'Sorry, der Login ist fehlgeschlagen.',
            'unauthorizedRedirect' => false,
            'authenticate' => [
                'Form' => [
                    'userModel' => 'Users',
                    'fields' => [
                        'username' => 'email'
                    ],
                    'finder' => 'auth' // UserTable::findAuth
                ]
            ],
            'storage' => 'Session'
        ]);
        
        $this->paginate = [
            'limit' => 100000,
            'maxLimit' => 100000
        ];
           
    }
    
    protected function setNavigation()
    {
        $this->Page = TableRegistry::getTableLocator()->get('Pages');
        $conditions = [];
        $conditions['Pages.status'] = APP_ON;
        $pages = $this->Page->getThreaded($conditions);
        $pagesForHeader = [];
        $pagesForFooter = [];
        foreach ($pages as $page) {
            if ($page->menu_type == 'header') {
                $pagesForHeader[] = $page;
            }
            if ($page->menu_type == 'footer') {
                $pagesForFooter[] = $page;
            }
        }
        $this->set('pagesForHeader', $pagesForHeader);
        $this->set('pagesForFooter', $pagesForFooter);
    }

    /**
     * @param \Cake\Event\Event $event
     * @return \Cake\Http\Response|null
     */
    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);
        
        $this->set('useDefaultValidation', $this->useDefaultValidation);
        
        if (Configure::read('debug')) {
            Cache::disable();
        } else {
            
            // wenn eingeloggt, nur 1 sec cachen, damit das forum auf das gecachte html zugreifen kann
            $duration = '+1 day';
            if ($this->AppAuth->user()) {
                $duration = '+1 sec';
            }
            
            Cache::setConfig('navi', [
                'engine' => 'File',
                'duration' => $duration,
                'path' => CACHE . 'views',
                'serialize' => false
            ]);
            Cache::enable();
            
        }
        
        // fluxbb login
        if (Configure::read('AppConfig.fluxBbForumEnabled') && $this->AppAuth->user()) {
            $this->FluxBb->login($this->AppAuth->getUserUid());
        }
        
        if (!$this->request->getSession()->check('isMobile')) {
            $detect = new Mobile_Detect();
            $isMobile = false;
            if ($detect->isMobile() && !$detect->isTablet()) {
                $isMobile = true;
            }
            $this->request->getSession()->write('isMobile', $isMobile); // default value
        }
        
    }
    
    public function beforeRender(EventInterface $event)
    {
        
        parent::beforeRender($event);
        
        $this->setNavigation();
        $this->set('appAuth', $this->AppAuth);
        $this->set('loggedUser', $this->AppAuth->user());
    }
    
    public function isAuthorized($user)
    {
        return true;
    }

    /**
     * checks the url for parameter "preview"
     * @return boolean preview
     */
    protected function isPreview()
    {
        if (isset($this->request->getParam('pass')['1']) && $this->request->getParam('pass')['1'] == 'vorschau')
            return true;
        return false;
    }

    /**
     * wenn über den admin eine seite im preview-mode aufgerufen wird (/vorschau am ende der url)
     * und diese seite aber online ist, redirecten.
     * nur, wenn die seite offline ist, flash message anzeigen, sonst verwirrt es
     * den user
     * 
     * @param int status of the object
     * @param string url
     */
    protected function doPreviewChecks($status, $redirectUrl)
    {
        if ($status == APP_ON && $this->isPreview()) {
            $this->redirect($redirectUrl);
        }
        if ($status == APP_OFF && $this->isPreview()) {
            $this->AppFlash->setFlashError('Diese Seite ist offline und somit nicht öffentlich sichtbar.');
        }
    }

    /**
     * überprüft die url auf den parameter /vorschau
     * 
     * @param string $modelName
     * @param string url (zum zeitpunkt dieses methoden-aufrufes ist noch keine uid vorhanden, sondern nur die url
     * @return array $params status for cake-condition
     */
    protected function getPreviewConditions($modelName, $url)
    {
        $previewConditions = [];
        
        if ($this->$modelName->hasField('publish')) {
            $previewConditions = [
                'DATE('.$modelName . '.publish) <= DATE(NOW())'
            ];
        }
        
        // admins oder owner dürfen offline-content im preview-mode sehen
        if (! $this->AppAuth->isAdmin() && ! $this->AppAuth->isOwnerByModelNameAndUrl($modelName, $url))
            return $previewConditions;
        
        if ($this->isPreview()) {
            $previewConditions = [
                $modelName . '.status' . ' >= ' . APP_OFF
            ];
        }
        return $previewConditions;
    }

    public function setContext($object)
    {
        $className = Inflector::classify($this->name);
        $this->set('context', [
            'object' => $object,
            'className' => $className
        ]);
    }
    
    public function mergeCustomMetaTags($metaTags, $object)
    {
        if (!empty($object->metatag) && !empty($object->metatag->title)) {
            $metaTags['title'] = $object->metatag->title;
        }
        if (!empty($object->metatag) && !empty($object->metatag->description)) {
            $metaTags['description'] = $object->metatag->description;
        }
        if (!empty($object->metatag) && !empty($object->metatag->keywords)) {
            $metaTags['keywords'] = $object->metatag->keywords;
        }
        return $metaTags;
    }
    
    public function setReferer()
    {
        $this->set('referer', $this->getReferer());
    }
    
    public function getReferer()
    {
        return $this->request->getData('referer') ?? $_SERVER['HTTP_REFERER'] ?? '/';
    }

    /**
     * checks if detail page is allowed for current user group
     * currently implemented for pages and votings
     * 
     * @param array $groups
     * @throws NotFoundException
     */
    protected function doUserGroupAccessCheck($groups)
    {
        return true;
        
        $loggedUser = $this->AppAuth->getUser();
        $loggedUserGroups = [];
        if (! empty($loggedUser->groups)) {
            $loggedUserGroups = Hash::extract($loggedUser->groups, '{n}.id');
        }
        
        $objectGroups = [];
        if (! empty($groups)) {
            $objectGroups = Hash::extract($groups, '{n}.id');
        }
        
        if (! $this->AppAuth->user()) {
            // ausgeloggt und page hat rechte gesetzt => 404
            if (! empty($objectGroups)) {
                throw new NotFoundException('user nicht eingeloggt und page verlangt view-rechte');
            }
        } else {
            // eingeloggt und logged user hat keine rechte => 404
            if (! empty($objectGroups)) {
                $loggedUserHasRightsToViewPage = false;
                foreach ($objectGroups as $objectGroup) {
                    if (in_array($objectGroup, $loggedUserGroups)) {
                        $loggedUserHasRightsToViewPage = true;
                    }
                }
                if (! $loggedUserHasRightsToViewPage) {
                    throw new NotFoundException('eingeloggter user besitzt keine view-rechte für diese seite');
                }
            }
        }
    }
    
    protected function patchEntityWithCurrentlyUpdatedFields($entity)
    {
        $modelName = $this->modelName;
        $entity = $this->$modelName->patchEntity($entity, [
            'currently_updated_by' => 0,
            'currently_updated_start' => new Time()
        ]);
        return $entity;
    }
    
    protected function stripTagsFromFields($entity, $modelName)
    {
        foreach ($entity->toArray() as $field => $data) {
            if (in_array($field, $this->$modelName->allowedBasicHtmlFields)) {
                $entity->$field = strip_tags($data, ALLOWED_TAGS_USER);
            } else if ($field == 'text') {
                // ckeditor feld heißt normalerweise 'text'
                $allowedTags = ALLOWED_TAGS_CKEDITOR_USER;
                if ($this->AppAuth->isAdmin() && in_array($modelName, ['Post', 'Page'])) {
                    $allowedTags =  ALLOWED_TAGS_CKEDITOR_ADMIN;
                }
                $entity->$field = strip_tags($data, $allowedTags);
            } else {
                if (is_string($data)) {
                    $entity->$field = strip_tags($data);
                }
            }
        }
        return $entity;
    }
    
    public function setIsCurrentlyUpdated($uid)
    {
        $this->set('isCurrentlyUpdated', $this->isCurrentlyUpdated($uid) ? '1' : '0');
    }
    
    /**
     * @param int $uid
     * @return boolean $success
     */
    protected function isCurrentlyUpdated($uid)
    {
        $modelName = $this->modelName;
        $data = $this->$modelName->find('all', [
            'conditions' => [
                $this->pluralizedModelName . '.uid' => $uid,
                $this->pluralizedModelName . '.status >= ' . APP_DELETED
            ],
            'contain' => [
                'CurrentlyUpdatedByUsers'
            ]
        ]);
        
        $data = $data->first();
        $diffInSeconds = 0;
        
        if ($data->currently_updated_start) {
            $diffInSeconds = Configure::read('AppConfig.timeHelper')->datediff(strtotime(date('Y-m-d H:i:s')), strtotime($data->currently_updated_start->format('Y-m-d H:i:s')));
        }
        
        if (! empty($data->currently_updated_by_user) && $data->currently_updated_by_user->uid != $this->AppAuth->getUserUid() && $data->currently_updated_by_user->uid > 0 && $diffInSeconds < 60 * 60) {
            $updatingUser = $data->currently_updated_by_user->firstname . ' ' . $data->currently_updated_by_user->lastname;
            $this->AppFlash->setFlashError('<b>Diese Seite ist gesperrt. ' . $updatingUser . ' hat ' . Configure::read('AppConfig.timeHelper')->timeAgoInWords($data->currentlyUpdatedStart) . ' begonnen, sie zu bearbeiten. <a id="unlockEditPageLink" href="javascript:void(0);">Entsperren?</a></b>');
            return true;
        }
        
        // if not currently updated, set logged user as updating one
        $saveData = [
            'currently_updated_by' => $this->AppAuth->getUserUid(),
            'currently_updated_start' => new Time()
        ];
        $entity = $this->$modelName->patchEntity($data, $saveData);
        $this->$modelName->save($entity);
        
        return false;
    }
    
    protected function getLatLngFromGeoCodingService($addressString) {
        
        if (Configure::read('googleMapApiKey') == '') {
            throw new ServiceUnavailableException('googleMapApiKey not defined');
        }
        
        $lat = 'ungültig';
        $lng = 'ungültig';
        
        $addressString = Configure::read('AppConfig.htmlHelper')->replaceAddressAbbreviations($addressString);
        $geocode = file_get_contents('https://maps.googleapis.com/maps/api/geocode/json?key='.Configure::read('googleMapApiKey').'&address=' . urlencode($addressString));
        $output = json_decode($geocode);
        
        if ($output->status == 'OK' && empty($output->results[0]->partial_match)) {
            $lat = str_replace(',', '.', $output->results[0]->geometry->location->lat);
            $lng = str_replace(',', '.', $output->results[0]->geometry->location->lng);
        } else {
            $this->log(json_encode($output));
        }
        
        return ['lat' => $lat, 'lng' => $lng];
    }
    
}
