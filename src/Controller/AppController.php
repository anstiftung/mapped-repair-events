<?php
declare(strict_types=1);
namespace App\Controller;

use App\Controller\Component\CommonComponent;
use App\Controller\Component\StringComponent;
use App\Model\Table\RootsTable;
use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Event\EventInterface;
use Cake\Utility\Inflector;
use Cake\I18n\DateTime;
use App\Model\Table\PagesTable;
use App\Services\GeoService;
use App\Model\Table\WorkshopsTable;
use App\Test\Mock\GeoServiceMock;
use Cake\Datasource\EntityInterface;
use Cake\ORM\Table;

class AppController extends Controller
{

    public string $modelName;
    public mixed $loggedUser = null;
    public RootsTable $Root;
    public WorkshopsTable $Workshop;
    public string $pluralizedModelName;
    public CommonComponent $Common;
    public StringComponent $String;
    public PagesTable $Page;
    public GeoService|GeoServiceMock $geoService;

    public function initialize(): void
    {
        parent::initialize();

        $this->Root = $this->getTableLocator()->get('Roots');
        $this->Workshop = $this->getTableLocator()->get('Workshops');
        $this->modelName = Inflector::classify($this->name);
        $this->pluralizedModelName = Inflector::pluralize($this->modelName);
        $this->geoService = new GeoService();


        $this->loadComponent('Authentication.Authentication');
        $this->loadComponent('Common');
        $this->loadComponent('String');
        $this->loadComponent('AppFlash', [
            'clear' => true,
        ]);
        if (!$this->getRequest()->is('json') && !in_array($this->name, ['Events'])) {
            $this->loadComponent('FormProtection');
        }

        $this->paginate = [
            'limit' => 100000,
            'maxLimit' => 100000,
        ];

    }

    protected function setNavigation(): void
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

    public function beforeFilter(EventInterface $event): void
    {
        parent::beforeFilter($event);
        $this->loggedUser = $this->request->getAttribute('identity');
        if (!empty($this->loggedUser)) {
            $this->loggedUser = $this->loggedUser->getOriginalData();
        }
        $this->set('loggedUser', $this->loggedUser);
    }

    public function beforeRender(EventInterface $event): void
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
     */
    protected function isPreview(): bool
    {
        return isset($this->request->getParam('pass')['1']) && $this->request->getParam('pass')['1'] == 'vorschau';
    }

    /**
     * wenn über den admin eine seite im preview-mode aufgerufen wird (/vorschau am ende der url)
     * und diese seite aber aktiv ist, redirecten.
     * nur, wenn die seite inaktiv ist, flash message anzeigen, sonst verwirrt es
     * den user
     */
    protected function doPreviewChecks(int $status, string $redirectUrl): void
    {
        if ($status == APP_ON && $this->isPreview()) {
            $this->redirect($redirectUrl);
        }
        if ($status == APP_OFF && $this->isPreview()) {
            $this->AppFlash->setFlashError('Diese Seite ist inaktiv und somit nicht öffentlich sichtbar.');
        }
    }

    /**
     * @return string[]
     */
    protected function getPreviewConditions(string $modelName, string $url): array
    {
        $previewConditions = [];

        if ($this->$modelName->hasField('publish')) {
            $previewConditions = [
                'DATE('.$modelName . '.publish) <= DATE(NOW())'
            ];
        }

        // admins oder owner dürfen inaktiv-content im preview-mode sehen
        if ($this->isLoggedIn() && !$this->isAdmin() && !$this->loggedUser->isOwnerByModelNameAndUrl($modelName, $url))
            return $previewConditions;

        if ($this->isPreview()) {
            $previewConditions = [
                $modelName . '.status' . ' >= ' . APP_OFF
            ];
        }
        return $previewConditions;
    }

    public function setContext(EntityInterface $object): void
    {
        $className = Inflector::classify($this->name);
        $this->set('context', [
            'object' => $object,
            'className' => $className,
        ]);
    }

    /**
     * @param array<string, string> $metaTags
     * @return array<string, string>
     */
    public function mergeCustomMetaTags(array $metaTags, EntityInterface $object): array
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

    public function getPreparedReferer(): string
    {
        return htmlspecialchars_decode((string) $this->getRequest()->getData('referer'));
    }

    public function setReferer(): void
    {
        $this->set('referer', $this->getReferer());
    }

    public function getReferer(): string
    {
        $referer = $this->request->getData('referer') ?? $_SERVER['HTTP_REFERER'] ?? '/';
        $referer = $this->addRefererParamsToUrl($referer);
        return $referer;
    }

    public function addRefererParamsToUrl(string $url): string
    {
        if ($this->getRequest()->getQuery('refererParams')) {
            $url .= (str_contains($url, '?') ? '&' : '?') . 'refererParams=' . $this->getRequest()->getQuery('refererParams');
        }
        return $url;
    }

    protected function patchEntityWithCurrentlyUpdatedFields(EntityInterface $entity): EntityInterface
    {
        $modelInstance = $this->getModelInstance($this->modelName);
        $entity = $modelInstance->patchEntity($entity, [
            'currently_updated_by' => 0,
            'currently_updated_start' => new DateTime()
        ]);
        return $entity;
    }

    protected function getModelInstance(string $modelName): Table
    {
        if (isset($this->$modelName)) {
            $modelInstance = $this->$modelName;
        } else {
            $modelInstance = $this->getTableLocator()->get(Inflector::pluralize($modelName));
        }
        return $modelInstance;
    }

    protected function stripTagsFromFields(EntityInterface $entity, string $modelName): EntityInterface
    {
        $modelInstance = $this->getModelInstance($modelName);
        foreach ($entity->toArray() as $field => $data) {
            if (in_array($field, $modelInstance->allowedBasicHtmlFields)) {
                if (!is_null($data)) {
                    $entity->$field = strip_tags($data, ALLOWED_TAGS_USER);
                }
            } else if ($field == 'text') {
                // editor feld heißt normalerweise 'text'
                $allowedTags = ALLOWED_TAGS_EDITOR_USER;
                if ($this->isAdmin() && in_array($modelName, ['Post', 'Page', 'Knowledge'])) {
                    $allowedTags =  ALLOWED_TAGS_EDITOR_ADMIN;
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

    public function setIsCurrentlyUpdated(int $uid): void
    {
        $this->set('isCurrentlyUpdated', $this->isCurrentlyUpdated((int) $uid) ? '1' : '0');
    }

    protected function isCurrentlyUpdated(int $uid): bool
    {
        $modelInstance = $this->getModelInstance($this->modelName);
        $data = $modelInstance->find('all',
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
            $currentlyUpdatedStart = $data->currently_updated_start->getTimestamp();
            $diffInSeconds = time() - $currentlyUpdatedStart;
        }

        if (!empty($data->currently_updated_by_user && isset($currentlyUpdatedStart))
            && $data->currently_updated_by_user->uid != ($this->isLoggedIn() ? $this->loggedUser->uid : 0)
            && $data->currently_updated_by_user->uid > 0
            && $diffInSeconds < 60 * 60) {
            $updatingUser = $data->currently_updated_by_user->firstname . ' ' . $data->currently_updated_by_user->lastname;
            $this->AppFlash->setFlashError('<b>Diese Seite ist gesperrt. ' . $updatingUser . ' hat ' . Configure::read('AppConfig.timeHelper')->timeAgoInWords($currentlyUpdatedStart) . ' begonnen, sie zu bearbeiten. <a id="unlockEditPageLink" href="javascript:void(0);">Entsperren?</a></b>');
            return true;
        }

        // if not currently updated, set logged user as updating one
        $saveData = [
            'currently_updated_by' => $this->isLoggedIn() ? $this->loggedUser->uid : 0,
            'currently_updated_start' => new DateTime()
        ];
        $entity = $modelInstance->patchEntity($data, $saveData);
        $modelInstance->save($entity);

        return false;
    }

}
