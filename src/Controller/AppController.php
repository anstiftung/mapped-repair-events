<?php
namespace App\Controller;

use App\Model\Table\RootsTable;
use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use Cake\Event\EventInterface;
use Cake\Http\Exception\NotFoundException;
use Cake\Http\Exception\ServiceUnavailableException;
use Cake\Utility\Hash;
use Cake\Utility\Inflector;
use Cake\I18n\DateTime;
use App\Model\Table\PagesTable;

class AppController extends Controller
{

    public $modelName;

    public $loggedUser = null;
    public $connection;
    public RootsTable $Root;
    public $pluralizedModelName;
    public $Common;
    public $String;
    public PagesTable $Page;

    public function __construct($request = null, $response = null)
    {
        parent::__construct($request, $response);
        $this->connection = ConnectionManager::get('default');
        $this->Root = $this->getTableLocator()->get('Roots');
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

        $this->loadComponent('Authentication.Authentication');
        $this->loadComponent('Common');
        $this->loadComponent('String');
        $this->loadComponent('AppFlash', [
            'clear' => true
        ]);
        if (!$this->getRequest()->is('json') && !in_array($this->name, ['Events'])) {
            $this->loadComponent('FormProtection');
        }

        $this->paginate = [
            'limit' => 100000,
            'maxLimit' => 100000
        ];

    }

    protected function setNavigation()
    {
        $this->Page = $this->getTableLocator()->get('Pages');
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
        $this->loggedUser = $this->request->getAttribute('identity');
        if (!empty($this->loggedUser)) {
            $this->loggedUser = $this->loggedUser->getOriginalData();
        }
        $this->set('loggedUser', $this->loggedUser);
    }

    public function beforeRender(EventInterface $event)
    {
        if (!$this->request->getSession()->check('isMobile') && $this->request->is('mobile')) {
            $this->request->getSession()->write('isMobile', true);
        }
        parent::beforeRender($event);
        $this->setNavigation();
    }

    protected function isLoggedIn(): bool
    {
        return $this->loggedUser !== null;
    }

    protected function isAdmin(): bool
    {
        return $this->isLoggedIn() && $this->loggedUser->isAdmin();
    }

    protected function isOrga(): bool
    {
        return $this->isLoggedIn() && $this->loggedUser->isOrga();
    }

    protected function isRepairhelper(): bool
    {
        return $this->isLoggedIn() && $this->loggedUser->isRepairhelper();
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
        if ($this->isLoggedIn() && !$this->isAdmin() && !$this->loggedUser->isOwnerByModelNameAndUrl($modelName, $url))
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

    public function getPreparedReferer()
    {
        return htmlspecialchars_decode($this->getRequest()->getData('referer'));
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

        $loggedUserGroups = [];
        if (!empty($this->loggedUser) && !empty($this->loggedUser->groups)) {
            $loggedUserGroups = Hash::extract($this->loggedUser->groups, '{n}.id');
        }

        $objectGroups = [];
        if (! empty($groups)) {
            $objectGroups = Hash::extract($groups, '{n}.id');
        }

        if (! $this->isLoggedIn()) {
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
            'currently_updated_start' => new DateTime()
        ]);
        return $entity;
    }

    protected function stripTagsFromFields($entity, $modelName)
    {
        foreach ($entity->toArray() as $field => $data) {
            if (in_array($field, $this->$modelName->allowedBasicHtmlFields)) {
                if (!is_null($data)) {
                    $entity->$field = strip_tags($data, ALLOWED_TAGS_USER);
                }
            } else if ($field == 'text') {
                // ckeditor feld heißt normalerweise 'text'
                $allowedTags = ALLOWED_TAGS_CKEDITOR_USER;
                if ($this->isAdmin() && in_array($modelName, ['Post', 'Page', 'Knowledge'])) {
                    $allowedTags =  ALLOWED_TAGS_CKEDITOR_ADMIN;
                }
                if (!is_null($data)) {
                    $entity->$field = strip_tags($data, $allowedTags);
                }
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
        $data = $this->$modelName->find('all',
            conditions: [
                $this->pluralizedModelName . '.uid' => $uid,
                $this->pluralizedModelName . '.status >= ' . APP_DELETED,
            ],
            contain: [
                'CurrentlyUpdatedByUsers',
            ]
        );

        $data = $data->first();
        $diffInSeconds = 0;
        if ($data->currently_updated_start) {
            $currentlyUpdatedStart = strtotime($data->currently_updated_start->format('Y-m-d H:i:s'));
            $diffInSeconds = time() - $currentlyUpdatedStart;
        }

        if (! empty($data->currently_updated_by_user) && $data->currently_updated_by_user->uid != $this->isLoggedIn() ? $this->loggedUser->uid : 0 && $data->currently_updated_by_user->uid > 0 && $diffInSeconds < 60 * 60) {
            $updatingUser = $data->currently_updated_by_user->firstname . ' ' . $data->currently_updated_by_user->lastname;
            $this->AppFlash->setFlashError('<b>Diese Seite ist gesperrt. ' . $updatingUser . ' hat ' . Configure::read('AppConfig.timeHelper')->timeAgoInWords($data->currentlyUpdatedStart) . ' begonnen, sie zu bearbeiten. <a id="unlockEditPageLink" href="javascript:void(0);">Entsperren?</a></b>');
            return true;
        }

        // if not currently updated, set logged user as updating one
        $saveData = [
            'currently_updated_by' => $this->isLoggedIn() ? $this->loggedUser->uid : 0,
            'currently_updated_start' => new DateTime()
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
        $addressString = trim($addressString);
        $geocode = file_get_contents('https://maps.googleapis.com/maps/api/geocode/json?key='.Configure::read('googleMapApiKey').'&address=' . urlencode($addressString));
        $output = json_decode($geocode);

        if ($output->status == 'OK') {
            $lat = str_replace(',', '.', $output->results[0]->geometry->location->lat);
            $lng = str_replace(',', '.', $output->results[0]->geometry->location->lng);
        }

        return ['lat' => $lat, 'lng' => $lng];
    }

}
