<?php
declare(strict_types=1);
namespace App\Controller;

use App\Controller\Component\StringComponent;
use App\Model\Table\WorkshopsTable;
use Cake\Core\Configure;
use Cake\Event\EventInterface;
use Cake\Http\Exception\ForbiddenException;
use Cake\Http\Exception\NotFoundException;
use Cake\Utility\Hash;
use Cake\Utility\Inflector;
use Cake\ORM\Query;
use Cake\View\JsonView;
use App\Model\Table\CategoriesTable;
use App\Model\Table\CountriesTable;
use App\Model\Table\InfoSheetsTable;
use App\Model\Table\PostsTable;
use App\Model\Table\UsersTable;
use App\Model\Table\WorknewsTable;
use App\Model\Entity\Worknews;
use App\Mailer\AppMailer;
use App\Model\Entity\Workshop;
use Cake\Http\Response;
use App\Model\Entity\User;

class WorkshopsController extends AppController
{

    public WorkshopsTable $Workshop;
    public CountriesTable $Country;
    public UsersTable $User;
    public InfoSheetsTable $InfoSheet;
    public PostsTable $Post;
    public WorknewsTable $Worknews;

    public function beforeFilter(EventInterface $event): void
    {

        parent::beforeFilter($event);
        $this->Workshop = $this->getTableLocator()->get('Workshops');
        $this->Authentication->allowUnauthenticated([
            'ajaxGetAllWorkshopsForMap',
            'ajaxGetWorkshopDetail',
            'ajaxGetWorkshopsAndUsersForTags',
            'getWorkshopsForHyperModeWebsite',
            'getWorkshopsWithCityFilter',
            'home',
            'cluster',
            'detail',
            'all',
        ]);
    }

    public function initialize(): void
    {
        parent::initialize();
        $this->addViewClasses([JsonView::class]);
    }

    public function add(): void
    {
        $workshop = $this->Workshop->newEntity(
            ['status' => APP_OFF],
            ['validate' => false]
        );
        $this->set('metaTags', ['title' => 'Initiative erstellen']);
        $this->_edit($workshop, false);

        // assures rendering of success message on redirected page and NOT before and then not showing it
        if (empty($this->request->getData())) {
            $this->render('edit');
        }
    }

    public function edit(int $uid): void
    {
        $workshop = $this->Workshop->find('all',
        conditions: [
            'Workshops.uid' => $uid,
            'Workshops.status >= ' . APP_DELETED
        ],
        contain: [
            'Metatags',
            'Categories'
        ])->first();

        if (empty($workshop)) {
            throw new NotFoundException;
        }
        $this->setIsCurrentlyUpdated($workshop->uid);
        $this->set('metaTags', ['title' => 'Initiative bearbeiten']);
        $this->_edit($workshop, true);

    }

    private function _edit(Workshop $workshop, bool $isEditMode): void
    {

        $this->User = $this->getTableLocator()->get('Users');
        $this->Workshop = $this->getTableLocator()->get('Workshops');
        $this->Country = $this->getTableLocator()->get('Countries');

        /** @var \App\Model\Table\CategoriesTable */
        $categoriesTable = $this->getTableLocator()->get('Categories');
        $this->set('categories', $categoriesTable->getForDropdown([APP_ON]));

        $this->set('uid', $workshop->uid);

        $this->setReferer();

        if (!empty($this->request->getData())) {

            if (!$this->request->getData('Workshops.use_custom_coordinates')) {
                $addressString = $this->request->getData('Workshops.street') . ', ' . $this->request->getData('Workshops.zip') . ' ' . $this->request->getData('Workshops.city') . ', ' . $this->request->getData('Workshops.country_code');
                $geoData = $this->geoService->getGeoDataByAddress($addressString);

                $this->request = $this->request->withData('Workshops.lat', $geoData['lat']);
                $this->request = $this->request->withData('Workshops.lng', $geoData['lng']);

                if ($geoData['lat'] == 'ungültig' || $geoData['lng'] == 'ungültig') {
                    $this->AppFlash->setFlashError('Zur eingegebenen Adresse wurden keine Koordinaten gefunden. Bitte klicke auf "Koordinaten selber festlegen" und trage die Koordinaten selbst ein.');
                }

            }

            if ($this->request->getData('Workshops.use_custom_coordinates')) {
                $geoData = $this->geoService->getGeoDataByCoordinates($this->request->getData('Workshops.lat'), $this->request->getData('Workshops.lng'));
                $this->request = $this->request->withData('Workshops.lat', str_replace(',', '.', $this->request->getData('Workshops.lat')));
                $this->request = $this->request->withData('Workshops.lng', str_replace(',', '.', $this->request->getData('Workshops.lng')));
            }
            $this->request = $this->request->withData('Workshops.province_id', $geoData['provinceId'] ?? 0);

            $patchedEntity = $this->Workshop->getPatchedEntityForAdminEdit($workshop, $this->request->getData());

            $errors = $patchedEntity->getErrors();

            if (isset($errors['lat']) && isset($errors['lat']['geoCoordinatesInBoundingBox'])) {
                $this->AppFlash->setFlashError($errors['lat']['geoCoordinatesInBoundingBox']);
            }

            if (empty($errors)) {

                $patchedEntity = $this->patchEntityWithCurrentlyUpdatedFields($patchedEntity);
                $entity = $this->stripTagsFromFields($patchedEntity, 'Workshop');

                if ($this->Workshop->save($entity)) {

                    $this->AppFlash->setFlashMessage($this->Workshop->name_de . ' erfolgreich gespeichert.');

                    // add orga user to workshop if workshop was created - id is kinda hard to retrieve...
                    if (!$isEditMode && $this->isOrga() && !$this->isAdmin()) {
                        $usersWorkshop = $this->getTableLocator()->get('UsersWorkshops');
                        $savedWorkshop = $this->Workshop->find('all', conditions: [
                            'Workshops.url' => $patchedEntity->url,
                            'Workshops.status >= ' => APP_DELETED
                        ])->first();
                        if (!empty($savedWorkshop)) {
                            $usersWorkshop->addApprovedUser($savedWorkshop->uid, $this->isLoggedIn() ? $this->loggedUser->uid : 0);
                        }
                    }

                    $userAction = 'erstellt';
                    if ($isEditMode) {
                        $userAction = 'geändert';
                    }

                    if (!$this->isAdmin()) {
                        $email = new AppMailer();
                        $email->viewBuilder()->setTemplate('workshop_added_or_changed');
                        $email->setSubject('Initiative "'.$entity->name.'" erfolgreich ' . $userAction)
                        ->setViewVars([
                            'workshop' => $entity,
                            'username' => $this->loggedUser->name,
                            'userAction' => $userAction,
                        ]);
                        $email->setTo(Configure::read('AppConfig.notificationMailAddress'));
                        $email->addToQueue();
                    }

                    $this->redirect($this->getPreparedReferer());

                } else {
                    $this->AppFlash->setFlashError($this->Workshop->name_de . ' <b>nicht</b>erfolgreich gespeichert.');
                }

            } else {
                $workshop = $patchedEntity;
            }
        }

        $this->set('countries', $this->Country->getForDropdown());

        $this->set('workshop', $workshop);
        $this->set('isEditMode', $isEditMode);

        if (!empty($errors)) {
            $this->render('edit');
        }
    }

    /**
     * @param \App\Model\Entity\Category[]|null $categories
     */
    private function getPreparedCategoryIcons(?array $categories): array
    {
        $preparedCategories = [];
        $i = 0;
        if (!empty($categories)) {
            foreach($categories as $category) {
                if ($i >= 5) break;
                $preparedCategories[] = Configure::read('AppConfig.serverName') . '/img/icons-skills/' . $category->icon . '.png';
                $i++;
            }
        }
        return $preparedCategories;
    }

    public function ajaxGetWorkshopsAndUsersForTags(): void
    {

        $this->request = $this->request->withParam('_ext', 'json');

        if (!$this->request->is('ajax')) {
            throw new ForbiddenException();
        }

        $tags = h($this->request->getQuery('tags')) ?? [];
        if (empty($tags)) {
            throw new NotFoundException('no tag passed');
        }

        $tagCategoriesAssociation = [
            '3dreparieren' => [630],
        ];
        $implementedTags = array_keys($tagCategoriesAssociation);

        $categoryIds = [];
        foreach($tags as $tag) {
            if (in_array($tag, $implementedTags)) {
                $categoryIds = array_merge($tagCategoriesAssociation[$tag], $categoryIds);
            }
        }
        $categoryIds = array_unique($categoryIds);

        /** @var \App\Model\Table\CategoriesTable */
        $categoriesTable = $this->getTableLocator()->get('Categories');
        $categories = [];
        $workshopsAssociation = $categoriesTable->getAssociation('Workshops');
        $workshopsAssociation->setConditions([
            'Workshops.status' => APP_ON,
        ]);
        if (!empty($categoryIds)) {
            $categories = $categoriesTable->find('all',
            conditions: [
                'Categories.status' => APP_ON,
                'Categories.id IN' => $categoryIds,
            ],
            contain: [
                'Workshops',
                'Users',
            ])->toArray();
        }

        if (empty($categories)) {
            $this->set([
                'status' => 1,
                'message' => 'ok',
                'workshops' => [],
                'users' => [],
            ]);
            $this->viewBuilder()->setOption('serialize', ['status', 'message', 'workshops', 'users']);
        }

        $workshopUids = Hash::extract($categories, '{n}.workshops.{n}.uid');
        $userUids = Hash::extract($categories, '{n}.users.{n}.uid');

        $preparedWorkshops = [];
        if (!empty($workshopUids)) {

            $this->Workshop = $this->getTableLocator()->get('Workshops');
            $categoriesAssociation = $this->Workshop->getAssociation('Categories');
            $categoriesAssociation->setConditions([
                'Categories.status' => APP_ON,
            ]);

            $workshops = $this->Workshop->find('all',
            conditions: [
                'Workshops.uid IN' => $workshopUids,
            ],
            contain: [
                'Categories' => [
                    'sort' => [
                        'Categories.name' => 'ASC', //3D-Reparatur should be first
                    ],
                ],
                'Countries',
            ],
            order: ['Workshops.name' => 'ASC']);

            foreach($workshops as $workshop) {
                $preparedWorkshops[] = [
                    'uid' => $workshop->uid,
                    'name' => $workshop->name,
                    'lat' => $workshop->lat,
                    'lng' => $workshop->lng,
                    'url' => Configure::read('AppConfig.serverName') . Configure::read('AppConfig.htmlHelper')->urlWorkshopDetail($workshop->url),
                    'street' => $workshop->street,
                    'zip' => $workshop->zip,
                    'city' => $workshop->city,
                    'website' => $workshop->website,
                    'image' => $workshop->image != '' ?  Configure::read('AppConfig.serverName') . Configure::read('AppConfig.htmlHelper')->getThumbs150Image($workshop->image, 'workshops') : Configure::read('AppConfig.serverName') . Configure::read('AppConfig.htmlHelper')->getThumbs100Image('rclogo-100.jpg', 'workshops'),
                    'country' => [
                        'name_de' => !empty($workshop->country) ? $workshop->country->name_de : '',
                    ],
                    'categories' => $this->getPreparedCategoryIcons($workshop->categories),
                ];
            }
        }

        $preparedUsers = [];
        if (!empty($userUids)) {

            $this->User = $this->getTableLocator()->get('Users');
            $users = $this->User->find('all',
            conditions: [
                'Users.uid IN' => $userUids,
            ],
            contain: [
                'Countries',
                'Skills' => function(Query $q) {
                    return $q->where(['Skills.status' => APP_ON]);
                },
                'Categories' => [
                    'sort' => [
                        'Categories.name' => 'ASC', //3D-Reparatur should be first
                    ],
                ],
                'Workshops' => function($q) {
                    return $q->where([
                        'UsersWorkshops.approved <> \'1970-01-01 00:00:00\'',
                    ]);
                }
            ],
            order: [
                'Users.firstname' => 'ASC',
                'Users.nick' => 'ASC',
            ]);

            $foundUserUids = [];
            foreach($users as $user) {

                if (in_array($user->uid, $foundUserUids)) {
                    continue;
                }

                $foundUserUids[] = $user->uid;

                $geoData = [];

                // add user's geo data as geoData object
                if (!is_null($user->lat) && !is_null($user->lng)) {
                    $geoData[] = [
                        'lat' => $user->lat,
                        'lng' => $user->lng,
                    ];
                }

                // add user's workshops geoData object
                foreach($user->workshops as $workshop) {
                    if (!is_null($workshop->lat) && !is_null($workshop->lng)) {
                        $geoData[] = [
                            'lat' => $workshop->lat,
                            'lng' => $workshop->lng,
                        ];
                    }
                }

                $preparedUsers[] = [
                    'uid' => $user->uid,
                    'nick' => $user->nick,
                    'firstname' => $user->firstname,
                    'lastname' => $user->lastname,
                    'city' => $user->city,
                    'website' => $user->website,
                    'email' => $user->email,
                    'url' => Configure::read('AppConfig.serverName') . Configure::read('AppConfig.htmlHelper')->urlUserProfile($user->uid),
                    'image' => Configure::read('AppConfig.serverName') . Configure::read('AppConfig.htmlHelper')->getUserProfileImageSrc($user, $user->image),
                    'country' => [
                        'name_de' => !empty($user->country) ? $user->country->name_de : '',
                    ],
                    'categories' => $this->getPreparedCategoryIcons($user->categories),
                    'geoData' => $geoData,
                ];
            }
        }
        $this->set([
            'status' => 1,
            'message' => 'ok',
            'workshops' => $preparedWorkshops,
            'users' => $preparedUsers,
        ]);

        $this->viewBuilder()->setOption('serialize', ['status', 'message', 'workshops', 'users']);

    }

    public function getWorkshopsWithCityFilter(): ?Response
    {

        /* @phpstan-ignore-next-line */
        $this->response = $this->response->cors($this->request)
            ->allowOrigin(['*'])
            ->allowMethods(['GET'])
            ->build();

        $this->request = $this->request->withParam('_ext', 'json');

        $city = $this->request->getQuery('city');
        if ($city === null || strlen((string) $city) < 3) {
            return $this->response->withStatus(400)->withType('json')->withStringBody(json_encode('city not passed or invalid (min 3 chars)'));
        }

        $workshops = $this->Workshop->find('all',
        conditions: [
            'Workshops.status' => APP_ON,
            'Workshops.city LIKE' => "{$city}%",
        ],
        contain: [
            'Categories' => [
                'sort' => [
                    'Categories.name' => 'asc',
                ]
            ],
            'Events' => function($q) {
                return $q->where([
                    'DATE_FORMAT(Events.datumstart, \'%Y-%m-%d\') >= DATE_FORMAT(NOW(), \'%Y-%m-%d\')',
                ]);
            },
        ],
        order: ['Workshops.name' => 'asc']);

        if ($workshops->count() == 0) {
            return $this->response->withStatus(404)->withType('json')->withStringBody(json_encode('no workshops found'));
        }

        $preparedWorkshops = [];
        foreach($workshops as $workshop) {

            $preparedCategories = [];
            foreach($workshop->categories as $category) {
                $preparedCategories[] = [
                    'id' => $category->id,
                    'label' => html_entity_decode((string) $category->name),
                    'iconUrl' => Configure::read('AppConfig.serverName') . '/img/icon-skills/' . $category->icon . '.png',
                ];
            }

            $nextEventDate = null;
            if (isset($workshop->events[0]) && !is_null($workshop->events[0]->datumstart)) {
                $nextEventDate = $workshop->events[0]->datumstart->i18nFormat(Configure::read('DateFormat.de.DateLong2'));
            }

            $preparedWorkshops[] = [
                'id' => $workshop->uid,
                'name' => html_entity_decode((string) $workshop->name),
                'city' => $workshop->city,
                'postalCode' => $workshop->zip,
                'street' => html_entity_decode((string) $workshop->street),
                'street2' => html_entity_decode((string) $workshop->adresszusatz),
                'coordinates' => [
                    'lat' => $workshop->lat,
                    'lng' => $workshop->lng,
                ],
                'landingPage' => Configure::read('AppConfig.serverName') . Configure::read('AppConfig.htmlHelper')->urlWorkshopDetail($workshop->url),
                'logoUrl' => $workshop->image != '' ?  Configure::read('AppConfig.serverName') . Configure::read('AppConfig.htmlHelper')->getThumbs150Image($workshop->image, 'workshops') : Configure::read('AppConfig.serverName') . Configure::read('AppConfig.htmlHelper')->getThumbs100Image('rclogo-100.jpg', 'workshops'),
                'category' => $preparedCategories,
                'nextEvent' => $nextEventDate,
            ];

        }

        $this->set([
            'workshops' => $preparedWorkshops,
        ]);
        $this->viewBuilder()->setOption('serialize', ['workshops']);
        return null;
    }

    public function getWorkshopsForHyperModeWebsite(): void
    {

        $this->request = $this->request->withParam('_ext', 'json');

        $workshops = $this->Workshop->find('all',
        conditions: [
            'Workshops.status' => APP_ON,
        ],
        contain: [
            'Categories' => [
                'sort' => [
                    'Categories.name' => 'ASC', //3D-Reparatur should be first
                ],
            ],
            'Countries',
        ],
        order: ['Workshops.created' => 'DESC']);

        $preparedWorkshops = [];
        foreach($workshops as $workshop) {
            $preparedWorkshops[] = [
                'name' => $workshop->name,
                'city' => $workshop->city,
                'url' => Configure::read('AppConfig.serverName') . Configure::read('AppConfig.htmlHelper')->urlWorkshopDetail($workshop->url),
                'image' => $workshop->image != '' ?  Configure::read('AppConfig.serverName') . Configure::read('AppConfig.htmlHelper')->getThumbs150Image($workshop->image, 'workshops') : Configure::read('AppConfig.serverName') . Configure::read('AppConfig.htmlHelper')->getThumbs100Image('rclogo-100.jpg', 'workshops'),
                'hasOwnLogo' => $workshop->image == '' ? false : true,
                'categories' => Hash::extract($workshop->categories, '{n}.name'),
            ];
        }

        $this->set([
            'workshops' => $preparedWorkshops,
        ]);
        $this->viewBuilder()->setOption('serialize', ['workshops']);

    }

    public function ajaxGetAllWorkshopsForMap(): void
    {

        if (!$this->request->is('ajax')) {
            throw new ForbiddenException();
        }

        $this->request = $this->request->withParam('_ext', 'json');

        $isWorkshopFilterSet = false;
        if (!empty($this->request->getQuery('workshopUid'))) {
            $isWorkshopFilterSet = true;
        }

        $keyword = '';
        $conditions = [
            'Workshops.status' => APP_ON
        ];

        $fields = [
            'Workshops.uid',
            'Workshops.url',
            'Workshops.lat',
            'Workshops.lng',
            'Workshops.name',
            'Workshops.street',
            'Workshops.zip',
            'Workshops.city',
            'Workshops.owner',
            'Workshops.country_code',
        ];

        $eventFields = [
            'Events.workshop_uid',
            'Events.uid',
            'Events.status',
            'Events.strasse',
            'Events.zip',
            'Events.ort',
            'Events.datumstart',
            'Events.uhrzeitstart',
            'Events.uhrzeitend',
            'Events.is_online_event',
        ];

        if ($isWorkshopFilterSet) {
            $eventFields = array_merge(
                $eventFields,
                [
                    'Events.lat',
                    'Events.lng',
                    'Events.eventbeschreibung',
                    'Events.veranstaltungsort',
                    'Events.land',
                    'Events.image',
                ]);
            $eventFields['isPast'] = 'DATE_FORMAT(Events.datumstart, \'%Y-%m-%d\') < DATE_FORMAT(NOW(), \'%Y-%m-%d\')';
        }

        $contain = [
            'Events' => function ($q) use ($eventFields) {
                $q->select($eventFields);
                return $q;
            },
        ];

        if ($this->isLoggedIn()) {
            $contain = array_merge($contain, [
                'Users' => [
                    'fields' => [
                        'UsersWorkshops.workshop_uid',
                        'Users.uid' // necessary to retrieve Users.Groups
                    ],
                ],
                'Users.Groups' => [
                    'fields' => [
                        'UsersGroups.user_uid',
                        'Groups.id'
                    ],
                ],
            ]);
        }

        if ($isWorkshopFilterSet) {
            $conditions['Workshops.uid'] = (int) $this->request->getQuery('workshopUid');
            $fields[] = 'Workshops.text';
            $contain = array_merge($contain, [
                'Events.Categories' => [
                    'fields' => [
                        'EventsCategories.event_uid',
                        'Categories.id',
                        'Categories.name',
                        'Categories.icon'
                    ],
                ],
            ]);
        }

        $eventsAssociation = $this->Workshop->getAssociation('Events');
        $eventsAssociation->setConditions([
            'Events.status >=' . APP_OFF,
            'Events.datumstart >= \'' . date('Y-m-01').'\'', // first day of current month
            'Events.datumstart != \'1970-01-01\''
        ]);

        $workshops = $this->Workshop->find('all',
        conditions: [
            $conditions
        ],
        fields: $fields,
        order: [
            'Workshops.name' => 'ASC'
        ],
        contain: $contain,
        );

        if (!empty($this->request->getQuery('keyword'))) {
            $keyword = h(strtolower(trim((string) $this->request->getQuery('keyword'))));
            if ($keyword !== '' && $keyword !== 'null' && $keyword !== WorkshopsTable::KEYWORD_FOR_WORKSHOPS_WITH_FUNDINGS) {
                $workshops->where($this->Workshop->getKeywordSearchConditions($keyword, true));
            }
            if ($keyword == WorkshopsTable::KEYWORD_FOR_WORKSHOPS_WITH_FUNDINGS) {
                $workshops->where([
                    'WorkshopFundings.submit_date IS NOT NULL', 
                ]);
                $workshops->contain([
                    'WorkshopFundings',
                ]);
            }
        }

        $workshops = $workshops->toArray();

        $categoriesTable = $this->getTableLocator()->get('Categories');
        $categories = $categoriesTable->getMainCategoriesForFrontend();

        $countriesTable = $this->getTableLocator()->get('Countries');
        $countriesMap = $countriesTable->getForDropdown();

        $preparedWorkshops = [];

        foreach ($workshops as &$workshop) {

            $hasModifyPermissions = $this->isLoggedIn() && ($this->isAdmin() || $this->Workshop->isUserInOrgaTeam($this->loggedUser, $workshop));
            $i = 0;

            foreach ($workshop->events as &$event) {

                if (!$hasModifyPermissions && $event->status == APP_OFF) {
                    unset($workshop->events[$i]);
                    $i++;
                    continue;
                }
                $event->wurl = $workshop->url;
                $event->eventname = $workshop->name;
                $event->hasModifyPermissions = $hasModifyPermissions;
                $event->directurl = Configure::read('AppConfig.htmlHelper')->urlEventDetail($workshop->url, $event->uid, $event->datumstart);

                // add category icons
                if ($isWorkshopFilterSet) {
                    $preparedEventCategories = [];
                    foreach($categories as $category) {
                        foreach($event->categories as $eventCategory) {
                            if ($category->id == $eventCategory->id) {
                                $preparedEventCategories[] = [
                                    'name' => $category->name,
                                    'icon' => $category->icon
                                ];
                            }
                        }
                    }
                    $event->categories = $preparedEventCategories;
                }

                // reduce size of json content
                unset($event->datumstart);
                unset($event->uhrzeitstart);
                unset($event->uhrzeitend);
                unset($workshop->users);

                $i++;

            }

            // special data structure needed
            $preparedWorkshop = [];

            $workshop['events'] = array_values($workshop['events']); // reindex array
            $workshop['country'] = [
                'name_de' => $countriesMap[$workshop->country_code] ?? '',
            ];
            unset($workshop->country_code);

            $tmpEvents = $workshop['events'];
            unset($workshop['events']);
            $preparedWorkshop['Workshop'] = $workshop;
            $preparedWorkshop['Workshop']['Events'] = $tmpEvents;
            $preparedWorkshops[] = $preparedWorkshop;

        }

        $this->set([
            'status' => 1,
            'message' => 'ok',
            'workshops' => $preparedWorkshops,
        ]);
        $this->viewBuilder()->setOption('serialize', ['status', 'message', 'workshops']);

    }

    public function home(): void
    {

        $this->Workshop = $this->getTableLocator()->get('Workshops');
        $latestWorkshops = $this->Workshop->getLatestWorkshops();
        $this->set('latestWorkshops', $latestWorkshops);

        $this->Post = $this->getTableLocator()->get('Posts');
        $latestPosts = $this->Post->getLatestPosts();
        $this->set('latestPosts', $latestPosts);

        $metaTags = Configure::read('AppConfig.metaTags.' . $this->request->getParam('controller') . '.' . $this->request->getParam('action'));
        $metaTags['description'] .= Configure::read('AppConfig.claim');
        $this->set('metaTags', $metaTags);

    }

    private function processWorknewsAddForm(Workshop $workshop): null
    {

        if (!empty($this->getRequest()->getData()) && ($this->getRequest()->getData('botEwX482') == '' || $this->getRequest()->getData('botEwX482') < 1)) {
            $this->redirect('/');
            return null;
        }

        $this->Worknews = $this->getTableLocator()->get('Worknews');
        $conditions = [
            'Worknews.workshop_uid' => $workshop->uid,
            'Worknews.confirm' => Worknews::STATUS_OK,
            'Worknews.email' => $this->isLoggedIn() ? $this->loggedUser->email : '',
        ];
        $worknews = $this->Worknews->find('all', conditions: $conditions)->first();

        if (!empty($this->request->getData())) {

            if (empty($this->request->getData('Worknews.workshop_uid'))) {
                throw new NotFoundException('workshop_uid not set');
            }

            $workshop = $this->Workshop->find('all', conditions: [
                'Workshops.uid' => $this->request->getData('Worknews.workshop_uid'),
                'Workshops.status >= ' . APP_DELETED
            ])->first();

            if (empty($workshop)) {
                throw new NotFoundException('workshop not found');
            }

            $confirmationCode = md5(StringComponent::createRandomString());
            $unsubscribeCode = md5(StringComponent::createRandomString());
            $mergedData = array_merge(
                $this->request->getData(),
                [
                    'confirm' => $confirmationCode,
                    'unsub' => $unsubscribeCode
                ]
            );
            $worknews = $this->Worknews->newEntity($mergedData);
            if (!($worknews->hasErrors())) {
                $this->Worknews->save($worknews);

                $email = new AppMailer();
                $email->viewBuilder()->setTemplate('activate_worknews');
                $email->setSubject(__('Please activate your worknews subscription'))
                    ->setViewVars([
                        'workshop' => $workshop,
                        'confirmationCode' => $confirmationCode,
                        'unsubscribeCode' => $unsubscribeCode
                ])->setTo($this->request->getData('Worknews.email'));

                $email->addToQueue();
                $this->AppFlash->setFlashMessage(__('Please activate your subscription using the activation link sent to') . ' ' . $this->request->getData('Worknews.email'));

            } else {
                $this->AppFlash->setFlashError('Es ist ein Fehler aufgetreten!');
            }

        } else {
            if (empty($worknews)) {
                // prefill field email with email of logged user
                if ($this->isLoggedIn()) {
                    $worknews = $this->Worknews->newEntity(
                        ['email' => $this->loggedUser->email], ['validate' => false]
                    );
                } else {
                    $worknews = $this->Worknews->newEmptyEntity();
                }
            }
        }
        $subscribed = $worknews->confirm == Worknews::STATUS_OK && $this->isLoggedIn() && $worknews->email == $this->loggedUser->email;
        $this->set('subscribed', $subscribed);
        $this->set('worknews', $worknews);
        return null;

    }

    public function detail(): void
    {

        if (! isset($this->request->getParam('pass')['0'])) {
            throw new NotFoundException('workshop not found');
        }
        $url = $this->request->getParam('pass')['0'];

        if ($url == '') {
            throw new NotFoundException('workshop not found');
        }

        $this->Workshop = $this->getTableLocator()->get('Workshops');
        $conditions = array_merge([
            'Workshops.url' => $url,
            'Workshops.status' => APP_ON
        ], $this->getPreviewConditions('Workshops', $url));

        $usersAssociation = $this->Workshop->getAssociation('Users');
        $usersAssociation->setConditions([
            'UsersWorkshops.approved <> \'1970-01-01 00:00:00\''
        ]);

        $eventsAssociation = $this->Workshop->getAssociation('Events');
        $eventsAssociation->setConditions([
            'DATE_FORMAT(Events.datumstart, \'%Y-%m-%d\') >= DATE_FORMAT(NOW(), \'%Y-%m-%d\')',
            'Events.status >= ' . APP_OFF
        ]);

        $contain = [
            'Categories',
            'Countries',
            'Metatags',
            'OwnerUsers',
            'Events',
            'Users' => function($q) {
                return $q->where([
                    'Users.status' => APP_ON,
                ]);
            },
            'Users.Groups',
        ];

        // mobile version does not include calendar and only shows bound events
        // it's the calendar component that fetches the workshop's events
        if ($this->request->getSession()->read('isMobile')) {
            /** @var \App\Model\Table\CategoriesTable */
            $categoriesTable = $this->getTableLocator()->get('Categories');
            $categories = $categoriesTable->find('all');
            $contain[] = 'Events.Categories';
        }

        $workshop = $this->Workshop->find('all',
        conditions: $conditions,
        contain: $contain)->first();

        if (empty($workshop)) {
            throw new NotFoundException('workshop not found');
        }

        $this->doPreviewChecks($workshop->status, Configure::read('AppConfig.htmlHelper')->urlWorkshopDetail($workshop->url));
        $this->setContext($workshop);

        $onlineEventsCount = 0;
        foreach($workshop->events as $event) {
            if ($event->status) {
                $onlineEventsCount++;
            }
        }
        $this->set('onlineEventsCount', $onlineEventsCount);

        $this->processWorknewsAddForm($workshop);

        $this->User = $this->getTableLocator()->get('Users');
        $orgaTeam = $this->Workshop->getOrgaTeam($workshop);
        $this->set('orgaTeam', $orgaTeam);

        $team = $this->Workshop->getTeam($workshop);
        $this->set('team', $team);

        $this->set('groups', Configure::read('AppConfig.htmlHelper')->getUserGroupsForWorkshopDetail());

        if ($this->request->getSession()->read('isMobile') && !empty($categories)) {

            $i = 0;
            $hasModifyPermissions = $this->isAdmin() || $this->Workshop->isUserInOrgaTeam($this->loggedUser, $workshop);

            foreach($workshop->events as $event) {

                if (!$hasModifyPermissions && $event->status == APP_OFF) {
                    unset($workshop->events[$i]);
                    $i++;
                    continue;
                }

                $workshop->events[$i]->eventname = $workshop->name;
                $preparedEventCategories = [];
                foreach($categories as $category) {
                    foreach($event->categories as $eventCategory) {
                        if ($category->id == $eventCategory->id) {
                            $preparedEventCategories[] = [
                                'name' => $category->name,
                                'icon' => $category->icon
                            ];
                        }
                    }
                    $event->prepared_categories = $preparedEventCategories;
                }
                $i++;
            }
        }

        $metaTags = [
            'title' => $workshop->name,
            'keywords' => $workshop->name . ', ' . $workshop->city . ', ' . Configure::read('AppConfig.metaTags.' . $this->request->getParam('controller') . '.' . $this->request->getParam('action') . '.keywords'),
        ];

        $descriptionCriterium = $workshop->_id % 3;

        $metaTagsConfig = 'AppConfig.metaTags.' . $this->request->getParam('controller') . '.' . $this->request->getParam('action');
        $descriptions = Configure::read($metaTagsConfig . '.descriptions-' . Configure::read('AppConfig.platformName'));
        if (empty($descriptions)) {
            $descriptions = Configure::read($metaTagsConfig . '.descriptions');
        }
        $description = $descriptions[$descriptionCriterium];
        $description = str_replace('%name%', $workshop->name, $description);
        $description = str_replace('%city%', $workshop->city ?? '', $description);
        $metaTags['description'] = $description;

        $metaTags = $this->mergeCustomMetaTags($metaTags, $workshop);
        $this->set('metaTags', $metaTags);

        $this->set('workshop', $workshop);

        $hasModifyPermissions = $this->isAdmin() || $this->Workshop->isUserInOrgaTeam($this->loggedUser, $workshop);
        $this->set('hasModifyPermissions', $hasModifyPermissions);

        $event = false;
        if (!empty($_GET['event'])) {
            $event = explode(',', (string) $_GET['event']);
            $event = count($event) == 2 ? $event : false;
        }
        $this->set('event', $event);

        $showStatistics = false;
        $this->InfoSheet = $this->getTableLocator()->get('InfoSheets');
        if ($this->InfoSheet->workshopInfoSheetsCount($workshop->uid) > 0 && $workshop->show_statistics > Workshop::STATISTICS_DISABLED) {
            $showStatistics = true;
        }
        $this->set('showStatistics', $showStatistics);

        $showCarbonFootprint = $workshop->show_statistics == Workshop::STATISTICS_SHOW_ALL;
        $this->set('showCarbonFootprint', $showCarbonFootprint);

    }

    private function checkType(string $type): array
    {
        if ($type != 'user') {
            throw new NotFoundException('wrong type');
        }

        return [
            'pluralized' => Inflector::pluralize($type),
            'upperPluralized' => ucfirst(Inflector::pluralize($type)),
            'resignMessage' => 'Du bist aus der Initiative ausgetreten.',
            'refuseMessage' => '%name% wurde erfolgreich als Mitarbeiter abgelehnt.',
            'approveMessage' => '%name% wurde erfolgreich als Mitarbeiter bestätigt und per E-Mail benachrichtigt.',
        ];
    }

    private function prepareUserWorkshopActions(): Workshop
    {
        if (! $this->isLoggedIn()) {
            throw new NotFoundException('nicht eingeloggt');
        }

        $type = $this->request->getParam('pass')[0];
        $preparedType = $this->checkType($type);

        $userUid = (int) $this->request->getParam('pass')[1];
        $workshopUid = (int) $this->request->getParam('pass')[2];

        if ($userUid <= 0 || $workshopUid <= 0) {
            throw new NotFoundException('wrong parameters');
        }

        switch ($type) {
            case 'user':
                $usersAssociation = $this->Workshop->getAssociation('Users');
                $usersAssociation->setConditions([
                    'Users.uid' => $userUid
                ]);
                break;
        }

        $query = $this->Workshop->find('all',
        conditions: [
            'Workshops.uid' => $workshopUid,
            'Workshops.status > ' => APP_DELETED
        ],
        contain: [
            'Users'
        ]);

        if (!$this->isAdmin()) {
            $query->matching('Users', function ($q) use ($userUid) {
                return $q->where([
                    'UsersWorkshops.user_uid' => $userUid
                ]);
            });
        }
        $workshop = $query->first();
        foreach($workshop->users as $user) {
            $user->revertPrivatizeData();
        }

        if (empty($workshop) || empty($workshop->{$preparedType['pluralized']})) {
            throw new NotFoundException('workshopUid: ' . $workshopUid . ' no ' . $type . '-workshop relation with userUid ' . $userUid . ' or not logged in as admin');
        }

        return $workshop;
    }

    public function userApprove(): void
    {
        $type = $this->request->getParam('pass')[0];
        $preparedType = $this->checkType($type);
        $workshop = $this->prepareUserWorkshopActions();
        $userEntity = $this->getUserEntity($workshop);
        $query = 'UPDATE ' . $preparedType['pluralized'] . '_workshops SET approved = NOW() WHERE workshop_uid = :workshopUid AND ' . $type . '_uid = :typeUid;';
        $params = [
            'workshopUid' => $workshop->uid,
            'typeUid' => $workshop->users[0]->uid
        ];

        $workshopsTable = $this->getTableLocator()->get('Workshops');
        $workshopsTable->getConnection()->getDriver()->prepare($query)->execute($params);

        /* START email-versand an anfrage-steller */
        $email = new AppMailer();
        $email->viewBuilder()->setTemplate('workshop_application_approved');
        $email->setSubject('Deine Anfrage zur Mitarbeit wurde bestätigt.')
            ->setViewVars([
            'workshop' => $workshop,
            'userEntity' => $userEntity
        ])
            ->setTo($userEntity->email)
            ->addToQueue();
        /* END email-versand an anfrage-steller */

        $this->AppFlash->setFlashMessage(str_replace('%name%', $userEntity->name, $preparedType['approveMessage']));
        $this->redirect($this->referer());
    }

    public function userRefuse(): void
    {
        $preparedType = $this->checkType($this->request->getParam('pass')[0]);
        $workshop = $this->userDelete($this->request->getParam('pass')[0]);
        $userEntity = $this->getUserEntity($workshop);
        $this->AppFlash->setFlashMessage(str_replace('%name%', $userEntity->name, $preparedType['refuseMessage']));
        $this->redirect($this->referer());
    }

    public function userResign(): void
    {
        $preparedType = $this->checkType($this->request->getParam('pass')[0]);
        $workshop = $this->userDelete($this->request->getParam('pass')[0]);
        $userEntity = $this->getUserEntity($workshop);
        $this->AppFlash->setFlashMessage(str_replace('%name%', $userEntity->name, $preparedType['resignMessage']));
        $this->redirect($this->referer());
    }

    public function userDelete(string $type): Workshop
    {
        $preparedType = $this->checkType($type);
        $workshop = $this->prepareUserWorkshopActions();
        $query = 'DELETE FROM '.$preparedType['pluralized'] . '_workshops'.' WHERE workshop_uid = :workshopUid AND ' . $type . '_uid = :typeUid;';
        $params = [
            'workshopUid' => $workshop->uid,
            'typeUid' => $workshop->users[0]->uid
        ];
        $workshopsTable = $this->getTableLocator()->get('Workshops');
        $workshopsTable->getConnection()->getDriver()->prepare($query)->execute($params);
        return $workshop;
    }

    private function getUserEntity(Workshop $workshop): User
    {
        return $workshop->users[0];
    }

    public function apply(string $relationTable, string $foreignKey, string $model, int $userUid): void
    {
        if (! empty($this->request->getData())) {

            $workshopUid = $this->request->getData($relationTable.'.workshop_uid');

            $query = 'REPLACE INTO ' . $relationTable . ' (' . $foreignKey . ', workshop_uid, created) VALUES(' . $userUid . ', ' . $workshopUid . ', NOW());';
            $workshopsTable = $this->getTableLocator()->get('Workshops');
            $workshopsTable->getConnection()->getDriver()->prepare($query)->execute();

            // immediately approve relation, if done by admin
            if ($this->isAdmin()) {
                $query = 'UPDATE ' . $relationTable . ' SET approved = NOW() WHERE workshop_uid = :workshopUid AND user_uid = :userUid';
                $params = [
                  'workshopUid' => $workshopUid,
                  'userUid' => $userUid
                ];
                $workshopsTable->getConnection()->getDriver()->prepare($query)->execute($params);
            }

            $userModel = Inflector::pluralize($model);
            $this->set('userModel', $userModel);
            $um = $this->getTableLocator()->get($userModel);
            $subject = 'Anfrage zur Mitarbeit bei deiner Initiative';
            $user = $um->find('all', conditions: [
                $userModel . '.uid' => $userUid,
                $userModel . '.status > ' => APP_DELETED,
            ])->first();
            $user->revertPrivatizeData();

            /* START email-versand an alle initiativen-orgas */
            if (!$this->isAdmin()) {
                $this->Workshop = $this->getTableLocator()->get('Workshops');
                $workshop = $this->Workshop->find('all',
                conditions: [
                    'Workshops.uid' => $workshopUid,
                    'Workshops.status > ' => APP_DELETED
                ],
                contain: [
                    'Users',
                    'Users.Groups'
                ])->first();

                $email = new AppMailer();
                $email->viewBuilder()->setTemplate('workshop_application');
                $email->setSubject($subject)
                    ->setViewVars([
                    'user' => $user,
                    'userModel' => $userModel,
                    'workshop' => $workshop,
                ]);

                $orgaTeam = $this->Workshop->getOrgaTeam($workshop);
                if (!empty($orgaTeam)) {
                    foreach($orgaTeam as $orgaUser) {
                        $orgaUser->revertPrivatizeData();
                        $email->addTo($orgaUser->email);
                    }
                } else {
                    $email->addTo(Configure::read('AppConfig.notificationMailAddress'));
                }
                $email->addToQueue();
            }
            /* END email-versand an alle orgas */

            $message = 'Deine Anfrage wurde gestellt. Bitte warte, bis sie vom Verantwortlichen der Initiative bestätigt wird. Er wurde per E-Mail benachrichtigt.';
            if ($this->isAdmin()) {
                $message = 'Die Zuordnung wurde erstellt und ist bereits bestätigt. Es wurden <b>keine</b> E-Mails versendet.';
            }
            $this->AppFlash->setFlashMessage($message);
            $redirectUrlMethod = 'urlUserWorkshopApplication' . Inflector::singularize($userModel);
            $this->redirect(Configure::read('AppConfig.htmlHelper')->$redirectUrlMethod());
        }

        $this->User = $this->getTableLocator()->get('Users');
        $this->Workshop = $this->getTableLocator()->get('Workshops');
        $associatedWorkshops = $this->Workshop->getWorkshopsWithUsers(APP_OFF);
        $associatedWorkshops->matching('Users', function ($q) use ($userUid) {
            return $q->where([
                'UsersWorkshops.user_uid' => $userUid,
            ]);
        });

        $workshopsWhereUserIsLastOrgaUser = $this->User->getWorkshopsWhereUserIsLastOrgaUser($associatedWorkshops);
        $workshopsWhereUserIsLastOrgaUserUids = [];
        foreach($workshopsWhereUserIsLastOrgaUser as $w) {
            $workshopsWhereUserIsLastOrgaUserUids[] = $w->uid;
        }
        $this->set('workshopsWhereUserIsLastOrgaUserUids', $workshopsWhereUserIsLastOrgaUserUids);

        $workshopsForDropdown = $this->Workshop->getForDropdown();

        if (!$this->isAdmin()) {
            // initiative aus dropdown löschen, damit nicht doppelt angefragt werden kann
            $i = 0;
            foreach ($associatedWorkshops as $associatedWorkshop) {
                unset($workshopsForDropdown[$associatedWorkshop->uid]);
                $i++;
            }
        }
        $this->set('associatedWorkshops', $associatedWorkshops);

        $this->set('workshopsForDropdown', $workshopsForDropdown);
    }

    public function applyAsUser(): void
    {

        $metaTags = [
            'title' => 'Mitmachen'
        ];
        $this->set('metaTags', $metaTags);

        // admins can apply in the name of another user
        $userUid = $this->isLoggedIn() ? $this->loggedUser->uid : 0;
        if ($this->isAdmin() && $this->request->getData('users_workshops.user_uid') > 0) {
            $userUid = (int) $this->request->getData('users_workshops.user_uid');
        }

        $this->apply('users_workshops', 'user_uid', 'Users', $userUid);

        if ($this->isAdmin()) {
            $this->User = $this->getTableLocator()->get('Users');
            $this->set('usersForDropdown', $this->User->getForDropdown());
        }

    }

    public function verwalten(): void
    {
        $workshopsTable = $this->getTableLocator()->get('Workshops');
        if ($this->isAdmin()) {
            $workshops = $workshopsTable->getWorkshopsWithUsers(APP_DELETED);
        } else {
            $workshops = $workshopsTable->getWorkshopsForAssociatedUser($this->loggedUser->uid, APP_DELETED);
        }

        $this->User = $this->getTableLocator()->get('Users');
        $workshopsWhereUserIsLastOrgaUser = $this->User->getWorkshopsWhereUserIsLastOrgaUser($workshops);
        $workshopsWhereUserIsLastOrgaUserUids = [];
        foreach($workshopsWhereUserIsLastOrgaUser as $w) {
            $workshopsWhereUserIsLastOrgaUserUids[] = $w->uid;
        }
        $this->set('workshopsWhereUserIsLastOrgaUserUids', $workshopsWhereUserIsLastOrgaUserUids);

        $metaTags = [
            'title' => 'Meine Initiativen (' . $workshops->count().')'
        ];
        $this->set('metaTags', $metaTags);

        $this->set('workshops', $workshops);

    }

    public function ajaxGetWorkshopDetail(int $workshopUid): void
    {

        if (!$this->request->is('ajax')) {
            throw new ForbiddenException();
        }

        $this->request = $this->request->withParam('_ext', 'json');

        $this->Workshop = $this->getTableLocator()->get('Workshops');

        $workshop = $this->Workshop->find('all',
        conditions: [
            'Workshops.uid' => $workshopUid,
            'Workshops.status > ' . APP_DELETED
        ],
        contain: [
            'Countries',
            'Categories'
        ])->first();
        $this->set([
            'status' => 0,
            'message' => 'ok',
            'workshop' => $workshop,
        ]);
        $this->viewBuilder()->setOption('serialize', ['status', 'message', 'workshop']);

    }

    public function all(): void
    {

        if (Configure::read('debug') && $this->isAdmin()) {
            ini_set('memory_limit', '1024M');
        }

        $metaTags = Configure::read('AppConfig.metaTags.' . $this->request->getParam('controller') . '.' . $this->request->getParam('action'));
        $metaTags['keywords'] = Configure::read('AppConfig.platformName') . ', ' . $metaTags['keywords'];
        $this->set('metaTags', $metaTags);

        $conditions = [
            'Workshops.status' => APP_ON
        ];

        $workshopsTable = $this->getTableLocator()->get('Workshops');
        $query = $workshopsTable->find('all',
        conditions: $conditions,
        contain: [
            'Events',
        ]);

        $keyword = '';
        if (!empty($this->request->getQuery('keyword'))) {
            $keyword = h(strtolower(trim((string) $this->request->getQuery('keyword'))));
            $query->where($workshopsTable->getKeywordSearchConditions($keyword, false));
        }
        $this->set('keyword', $keyword);

        $provincesTable = $this->getTableLocator()->get('Provinces');
        $provinceCounts = $workshopsTable->getProvinceCounts();
        $provinces = $provincesTable->getForDropdown($provinceCounts);
        $this->set('provinces', $provinces);

        $provinceId = $this->request->getQuery('provinceId', 0);
        $this->set('provinceId', $provinceId);

        if ($provinceId > 0) {
            $query->where([$workshopsTable->aliasField('province_id') => $provinceId]);
        }

        $workshops = $this->paginate($query, [
            'sortableFields' => [
                'Workshops.created', 'Workshops.zip', 'Workshops.city', 'Workshops.name'
            ],
            'order' => [
                'Workshops.created' => 'DESC'
            ]
        ]);
        $this->set('workshops', $workshops);

        $workshopsForMap = [];
        foreach($workshops as $workshop) {
            $eventsForMap = [];
            if ($workshop->events) {
                foreach($workshop->events as $event) {
                    $eventsForMap[] = [
                        'uhrzeitstart_formatted' => $event->uhrzeitstart_formatted,
                        'datumstart_formatted' => $event->datumstart_formatted,
                        'uhrzeitend_formatted' => $event->uhrzeitend_formatted,
                        'eventname' => $workshop->name,
                        'directurl' => $workshop->url.'?event='.$event->uid.','.$event->datumstart_formatted,
                        'lat' => $event->lat,
                        'lng' => $event->lng,
                    ];
                }
            }
            $workshopsForMap[] = [
                'Workshop' => [
                    'name' => $workshop->name,
                    'uid' => $workshop->uid,
                    'url' => $workshop->url,
                    'lat' => $workshop->lat,
                    'lng' => $workshop->lng
                ],
                'Events' => $eventsForMap
            ];
        }
        $this->set('workshopsForMap', $workshopsForMap);


        $urlOptions = [
            'url' => [
                'controller' => 'initiativen',
                'keyword' => $keyword
            ]
        ];
        $this->set('urlOptions', $urlOptions);

        if (!empty($this->request->getQuery('sort'))) {
            $this->set('canonicalUrl', '/orte');
        }

    }
}
?>
