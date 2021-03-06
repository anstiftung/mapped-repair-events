<?php
namespace App\Controller;

use App\Controller\Component\StringComponent;
use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use Cake\Event\EventInterface;
use Cake\I18n\Time;
use Cake\Mailer\Mailer;
use Cake\Http\Exception\ForbiddenException;
use Cake\Http\Exception\NotFoundException;
use Cake\Utility\Hash;
use Cake\Utility\Inflector;

class WorkshopsController extends AppController
{

    public function beforeFilter(EventInterface $event) {

        parent::beforeFilter($event);
        $this->Workshop = $this->getTableLocator()->get('Workshops');
        $this->connection = ConnectionManager::get('default');
        $this->AppAuth->allow([
            'ajaxGetAllWorkshopsForMap',
            'ajaxGetWorkshopDetail',
            'ajaxGetWorkshopsAndUsersForTags',
            'home',
            'cluster',
            'detail',
            'all',
        ]);

    }

    public function isAuthorized($user)
    {

        if ($this->request->getParam('action') == 'verwalten') {
            if (!($this->AppAuth->isAdmin() || $this->AppAuth->isOrga())) {
                return false;
            }
            return true;
        }


        // die action "edit" ist für alle eingeloggten user erlaubt, die orga-mitglieder der initiative sind
        if ($this->request->getParam('action') == 'add') {

            if ($this->AppAuth->isAdmin()) {
                $this->useDefaultValidation = true;
                return true;
            }

            if ($this->AppAuth->isOrga()) {
                return true;
            }

            return false;

        }

        if ($this->request->getParam('action') == 'edit') {

            if (!($this->AppAuth->isOrga() || $this->AppAuth->isAdmin())) {
                return false;
            }

            if ($this->AppAuth->isAdmin()) {
                $this->useDefaultValidation = false;
                return true;
            }

            $workshopUid = (int) $this->request->getParam('pass')[0];

            // all approved orgas are alloewed to edit and add workshops
            $this->Workshop = $this->getTableLocator()->get('Workshops');

            $workshop = $this->Workshop->getWorkshopForIsUserInOrgaTeamCheck($workshopUid);
            if ($this->Workshop->isUserInOrgaTeam($this->AppAuth->user(), $workshop)) {
                return true;
            }

            return false;
        }

        return parent::isAuthorized($user);

    }

    public function add()
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

    public function edit($uid)
    {

        if ($uid === null) {
            throw new NotFoundException;
        }

        $workshop = $this->Workshop->find('all', [
            'conditions' => [
                'Workshops.uid' => $uid,
                'Workshops.status >= ' . APP_DELETED
            ],
            'contain' => [
                'Metatags',
                'Categories'
            ]
        ])->first();

        if (empty($workshop)) {
            throw new NotFoundException;
        }
        $this->setIsCurrentlyUpdated($workshop->uid);
        $this->set('metaTags', ['title' => 'Initiative bearbeiten']);
        $this->_edit($workshop, true);

    }

    private function _edit($workshop, $isEditMode)
    {

        $this->User = $this->getTableLocator()->get('Users');
        $this->Workshop = $this->getTableLocator()->get('Workshops');
        $this->Country = $this->getTableLocator()->get('Countries');
        $this->Category = $this->getTableLocator()->get('Categories');
        $this->set('categories', $this->Category->getForDropdown(APP_ON));

        $this->set('uid', $workshop->uid);

        $this->setReferer();

        if (!empty($this->request->getData())) {

            if (!$this->request->getData('Workshops.use_custom_coordinates')) {
                $addressString = $this->request->getData('Workshops.street') . ', ' . $this->request->getData('Workshops.zip') . ' ' . $this->request->getData('Workshops.city') . ', ' . $this->request->getData('Workshops.country_code');
                $coordinates = $this->getLatLngFromGeoCodingService($addressString);
                $this->request = $this->request->withData('Workshops.lat', $coordinates['lat']);
                $this->request = $this->request->withData('Workshops.lng', $coordinates['lng']);
            }

            if ($this->request->getData('Workshops.use_custom_coordinates')) {
                $this->request = $this->request->withData('Workshops.lat', str_replace(',', '.', $this->request->getData('Workshops.lat')));
                $this->request = $this->request->withData('Workshops.lng', str_replace(',', '.', $this->request->getData('Workshops.lng')));
            }

            $patchedEntity = $this->Workshop->getPatchedEntityForAdminEdit($workshop, $this->request->getData(), $this->useDefaultValidation);

            $errors = $patchedEntity->getErrors();
            if (isset($errors['lat']) && isset($errors['lat']['numeric'])) {
                $this->AppFlash->setFlashError($errors['lat']['numeric']);
            }

            if (empty($errors)) {

                $patchedEntity = $this->patchEntityWithCurrentlyUpdatedFields($patchedEntity);
                $entity = $this->stripTagsFromFields($patchedEntity, 'Workshop');

                if ($this->Workshop->save($entity)) {

                    $this->AppFlash->setFlashMessage($this->Workshop->name_de . ' erfolgreich gespeichert.');

                    // add orga user to workshop if workshop was created - id is kinda hard to retrieve...
                    if (!$isEditMode && $this->AppAuth->isOrga() &&!$this->AppAuth->isAdmin()) {
                        $usersWorkshop = $this->getTableLocator()->get('UsersWorkshops');
                        $savedWorkshop = $this->Workshop->find('all', [
                            'conditions' => [
                                'Workshops.url' => $patchedEntity->url,
                                'Workshops.status >= ' => APP_DELETED
                            ]
                        ])->first();
                        if (!empty($savedWorkshop)) {
                            $usersWorkshop->addApprovedUser($savedWorkshop->uid, $this->AppAuth->getUserUid());
                        }
                    }

                    $userAction = 'erstellt';
                    if ($isEditMode) {
                        $userAction = 'geändert';
                    }
                    $email = new Mailer('default');
                    $email->viewBuilder()->setTemplate('workshop_added_or_changed');
                    $email->setSubject(Configure::read('AppConfig.initiativeNameSingular') . ' "'.$entity->name.'" erfolgreich ' . $userAction)
                    ->setViewVars([
                        'workshop' => $entity,
                        'username' => $this->AppAuth->getUserName(),
                        'userAction' => $userAction,
                    ]);
                    $email->setTo(Configure::read('AppConfig.notificationMailAddress'));
                    $email->send();

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
        $this->set('useDefaultValidation', $this->useDefaultValidation);

        if (!empty($errors)) {
            $this->render('edit');
        }
    }

    private function getPreparedCategoryIcons($categories)
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

    public function ajaxGetWorkshopsAndUsersForTags() {

        $this->RequestHandler->renderAs($this, 'json');

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

        $this->Category = $this->getTableLocator()->get('Categories');
        $categories = [];
        $workshopsAssociation = $this->Category->getAssociation('Workshops');
        $workshopsAssociation->setConditions([
            'Workshops.status' => APP_ON,
        ]);
        if (!empty($categoryIds)) {
            $categories = $this->Category->find('all', [
                'conditions' => [
                    'Categories.status' => APP_ON,
                    'Categories.id IN' => $categoryIds,
                ],
                'contain' => [
                    'Workshops',
                    'Users',
                ],
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

            $workshops = $this->Workshop->find('all', [
                'conditions' => [
                    'Workshops.uid IN' => $workshopUids,
                ],
                'contain' => [
                    'Categories' => [
                        'sort' => [
                            'Categories.name' => 'ASC', //3D-Reparatur should be first
                        ],
                    ],
                    'Countries',
                ],
                'order' => ['Workshops.name' => 'ASC'],
            ]);

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
            $users = $this->User->find('all', [
                'conditions' => [
                    'Users.uid IN' => $userUids,
                ],
                'contain' => [
                    'Countries',
                    'Skills',
                    'Categories' => [
                        'sort' => [
                            'Categories.name' => 'ASC', //3D-Reparatur should be first
                        ],
                    ]
                ],
                'order' => [
                    'Users.firstname' => 'ASC',
                    'Users.nick' => 'ASC',
                ],
            ]);

            $foundUserUids = [];
            foreach($users as $user) {
                if (in_array($user->uid, $foundUserUids)) {
                    continue;
                }
                $foundUserUids[] = $user->uid;
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

    public function ajaxGetAllWorkshopsForMap() {

        if (!$this->request->is('ajax')) {
            throw new ForbiddenException();
        }

        $this->RequestHandler->renderAs($this, 'json');

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
            'Countries.name_de'
        ];

        $addCategories = false;
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

        if (!empty($this->request->getQuery('workshopUid'))) {
            $conditions['Workshops.uid'] = (int) $this->request->getQuery('workshopUid');
            $fields[] = 'Workshops.text';
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
            $addCategories = true;
        }

        $eventsAssociation = $this->Workshop->getAssociation('Events');
        $eventsAssociation->setConditions([
            'Events.status >=' . APP_OFF,
            'Events.datumstart >= \'' . date('Y-m-01').'\'', // first day of current month
            'Events.datumstart != \'0000-00-00\''
        ]);

        $workshops = $this->Workshop->find('all', [
            'conditions' => [
                $conditions
            ],
            'fields' => $fields,
            'order' => [
                'Workshops.name' => 'ASC'
            ],
            'contain' => [
                'Countries',
                'Events' => function ($q) use ($eventFields) {
                    $q->select($eventFields);
                    return $q;
                },
                'Events.Categories' => [
                    'fields' => [
                        'EventsCategories.event_uid',
                        'Categories.id',
                        'Categories.name',
                        'Categories.icon'
                    ]
                ],
                'Users' => [
                    'fields' => [
                        'UsersWorkshops.workshop_uid',
                        'Users.uid' // necessary to retrieve Users.Groups
                    ]
                ],
                'Users.Groups' => [
                    'fields' => [
                        'UsersGroups.user_uid',
                        'Groups.id'
                    ]
                ]
            ]
        ]);

        if (!empty($this->request->getQuery('keyword'))) {
            $keyword = h(strtolower(trim($this->request->getQuery('keyword'))));
            if ($keyword !== '' && $keyword !== 'null') {
                $workshops->where($this->Workshop->getKeywordSearchConditions($keyword, true));
            }
        }

        $workshops = $workshops->toArray();

        $this->Category = $this->getTableLocator()->get('Categories');
        $categories = $this->Category->getMainCategoriesForFrontend();

        $preparedWorkshops = [];

        foreach ($workshops as &$workshop) {

            $hasModifyPermissions = $this->AppAuth->isAdmin() || $this->Workshop->isUserInOrgaTeam($this->AppAuth->user(), $workshop);
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
                if ($addCategories) {
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

                $i++;

            }

            // special data structure needed
            $preparedWorkshop = [];

            $tmpEvents = $workshop['events'];
            unset($workshop['events']);
            $preparedWorkshop['Workshop'] = $workshop;
            $preparedWorkshop['Workshop']['Events'] = $tmpEvents;
            $preparedWorkshops[] = $preparedWorkshop;

        }

        $this->set([
            'status' => 1,
            'message' => 'ok',
            'workshops' => $preparedWorkshops
        ]);
        $this->viewBuilder()->setOption('serialize', ['status', 'message', 'workshops']);

    }

    public function home() {

        $this->Workshop = $this->getTableLocator()->get('Workshops');
        $latestWorkshops = $this->Workshop->getLatestWorkshops();
        $this->set('latestWorkshops', $latestWorkshops);

        $this->Post = $this->getTableLocator()->get('Posts');
        $latestPosts = $this->Post->getLatestPosts();
        $this->set('latestPosts', $latestPosts);

        $metaTags = [
            'title' => Configure::read('AppConfig.initiativeNamePlural') . ' finden, unterstützen und gründen',
            'description' => 'Mach mit im Reparatur-Café! ' . Configure::read('AppConfig.initiativeNamePlural') . ' finden, unterstützen und gründen - Vernetzung, Beratung und Austausch',
            'keywords' => 'repair café, repair cafe, reparieren, repair, reparatur, reparatur-initiativen, netzwerk reparatur-initiativen'
        ];
        $this->set('metaTags', $metaTags);

    }

    private function processWorknewsAddForm($workshop)
    {

        if (!empty($this->getRequest()->getData()) && ($this->getRequest()->getData('botEwX482') == '' || $this->getRequest()->getData('botEwX482') < 3)) {
            $this->redirect('/');
            return;
        }

        $this->Worknews = $this->getTableLocator()->get('Worknews');
        $worknews = $this->Worknews->find('all', [
            'conditions' => [
                'Worknews.workshop_uid' => $workshop->uid,
                'Worknews.email' => $this->AppAuth->getUserEmail(),
                'Worknews.confirm' => 'ok'
            ]
        ])->first();

        if (!empty($this->request->getData())) {

            if (empty($this->request->getData('Worknews.workshop_uid'))) {
                throw new NotFoundException('workshop_uid not set');
            }

            $workshop = $this->Workshop->find('all', [
                'conditions' => [
                    'Workshops.uid' => $this->request->getData('Worknews.workshop_uid'),
                    'Workshops.status >= ' . APP_DELETED
                ]
            ])->first();

            if (empty($workshop)) {
                throw new NotFoundException('workshop not found');
            }

            $confirmationCode = md5(StringComponent::createRandomString());
            $unsubscribeCode = md5(StringComponent::createRandomString());
            $mergedData = array_merge(
                $this->request->getData(),
                [
                    'created' => Time::now(),
                    'modified' => Time::now(),
                    'confirm' => $confirmationCode,
                    'unsub' => $unsubscribeCode
                ]
            );
            $worknews = $this->Worknews->newEntity($mergedData);
            if (!($worknews->hasErrors())) {
                $this->Worknews->save($worknews);

                $email = new Mailer('default');
                $email->viewBuilder()->setTemplate('activate_worknews');
                $email->setSubject(__('Please activate your worknews subscription'))
                    ->setViewVars([
                        'workshop' => $workshop,
                        'confirmationCode' => $confirmationCode,
                        'unsubscribeCode' => $unsubscribeCode
                ])->setTo($this->request->getData('Worknews.email'));

                $email->send();
                $this->AppFlash->setFlashMessage(__('Please activate your subscription using the activation link sent to') . ' ' . $this->request->getData('Worknews.email'));

            } else {
                $this->AppFlash->setFlashError('Es ist ein Fehler aufgetreten!');
            }

        } else {
            if (empty($worknews)) {
                // prefill field email with email of logged user
                $worknews = $this->Worknews->newEntity(
                    ['email' => $this->AppAuth->getUserEmail()], ['validate' => false]
                );
            }
        }
        $subscribed = $worknews->confirm == 'ok' && $this->AppAuth->user() && $worknews->email == $this->AppAuth->getUserEmail();
        $this->set('subscribed', $subscribed);
        $this->set('worknews', $worknews);

    }

    public function detail()
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
            'UsersWorkshops.approved <> \'0000-00-00 00:00:00\''
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
            'Users',
            'Events',
            'Users.Groups'
        ];

        // mobile version does not include calendar and only shows bound events
        // it's the calendar component that fetches the workshop's events
        if ($this->request->getSession()->read('isMobile')) {
            $this->Category = $this->getTableLocator()->get('Categories');
            $categories = $this->Category->find('all');
            $contain[] = 'Events.Categories';
        }

        $workshop = $this->Workshop->find('all', [
            'conditions' => $conditions,
            'contain' => $contain
        ])->first();

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
            foreach($workshop->events as $event) {
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
            'keywords' => $workshop->name . ', ' . $workshop->city . ', repair café, repair-café, repair, reparatur, repaircafé, reparieren, reparatur café, reparatur-initiativen'
        ];

        $descriptionCriterium = $workshop->_id % 3;
        switch($descriptionCriterium) {
            case 0:
                $description = $workshop->name . ' hilft: Elektrogeräte, Unterhaltungselektronik, Kleidung uvm. gemeinsam reparieren in '.$workshop->city.'. Mach mit!';
                break;
            case 1:
                $description = $workshop->name . ': Gemeinsam reparieren in '.$workshop->city.'.';
                break;
            case 2:
                $description = $workshop->name . ' - mach mit! Gemeinsam Elektrogeräte, Unterhaltungselektronik, Kleidung uvm. reparieren in '.$workshop->city.'.';
                break;
        }

        $metaTags['description'] = $description;

        $metaTags = $this->mergeCustomMetaTags($metaTags, $workshop);
        $this->set('metaTags', $metaTags);

        $this->set('workshop', $workshop);

        $hasModifyPermissions = $this->AppAuth->isAdmin() || $this->Workshop->isUserInOrgaTeam($this->AppAuth->user(), $workshop);
        $this->set('hasModifyPermissions', $hasModifyPermissions);

        $event = false;
        if (!empty($_GET['event'])) {
            $event = explode(',', $_GET['event']);
            count($event) == 2 or $event = false;
        }
        $this->set('event', $event);

        $showStatistics = false;
        $this->InfoSheet = $this->getTableLocator()->get('InfoSheets');
        if ($this->InfoSheet->workshopInfoSheetsCount($workshop->uid) > 0 && $workshop->show_statistics > $this->Workshop::STATISTICS_DISABLED) {
            $showStatistics = true;
        }
        $this->set('showStatistics', $showStatistics);

        $showCarbonFootprint = $workshop->show_statistics == $this->Workshop::STATISTICS_SHOW_ALL;
        $this->set('showCarbonFootprint', $showCarbonFootprint);

    }

    private function checkType($type)
    {
        if (! in_array($type, [
            'user',
        ])) {
            throw new NotFoundException('wrong type');
        }

        $preparedType = [
            'pluralized' => Inflector::pluralize($type),
            'upperPluralized' => ucfirst(Inflector::pluralize($type))
        ];
        if ($type == 'user') {
            $preparedType['resignMessage'] = 'Du bist aus der Initiative ausgetreten.';
            $preparedType['refuseMessage'] = '%name% wurde erfolgreich als Mitarbeiter abgelehnt.';
            $preparedType['approveMessage'] = '%name% wurde erfolgreich als Mitarbeiter bestätigt und per E-Mail benachrichtigt.';
        }

        return $preparedType;
    }

    /**
     * @return $workshop
     */
    private function prepareUserWorkshopActions()
    {
        if (! $this->AppAuth->user()) {
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

        $query = $this->Workshop->find('all', [
            'conditions' => [
                'Workshops.uid' => $workshopUid,
                'Workshops.status > ' => APP_DELETED
            ],
            'contain' => [
                'Users'
            ]
        ]);

        if (!$this->AppAuth->isAdmin()) {
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

    public function userApprove()
    {
        $type = $this->request->getParam('pass')[0];
        $preparedType = $this->checkType($type);
        $workshop = $this->prepareUserWorkshopActions();
        $userEntity = $this->getUserEntity($preparedType['pluralized'], $workshop);
        $query = 'UPDATE ' . $preparedType['pluralized'] . '_workshops SET approved = NOW() WHERE workshop_uid = :workshopUid AND ' . $type . '_uid = :typeUid;';
        $params = [
            'workshopUid' => $workshop->uid,
            'typeUid' => $workshop->users[0]->uid
        ];
        $this->connection->execute($query, $params);

        /* START email-versand an anfrage-steller */
        $email = new Mailer('default');
        $email->viewBuilder()->setTemplate('workshop_application_approved');
        $email->setSubject('Deine Anfrage zur Mitarbeit wurde bestätigt.')
            ->setViewVars([
            'workshop' => $workshop,
            'userEntity' => $userEntity
        ])
            ->setTo($userEntity->email)
            ->send();
        /* END email-versand an anfrage-steller */

        $this->AppFlash->setFlashMessage(str_replace('%name%', $userEntity->name, $preparedType['approveMessage']));
        $this->redirect($this->referer());
    }

    public function userRefuse()
    {
        $preparedType = $this->checkType($this->request->getParam('pass')[0]);
        $workshop = $this->userDelete($this->request->getParam('pass')[0]);
        $userEntity = $this->getUserEntity($preparedType['pluralized'], $workshop);
        $this->AppFlash->setFlashMessage(str_replace('%name%', $userEntity->name, $preparedType['refuseMessage']));
        $this->redirect($this->referer());
    }

    public function userResign()
    {
        $preparedType = $this->checkType($this->request->getParam('pass')[0]);
        $workshop = $this->userDelete($this->request->getParam('pass')[0]);
        $userEntity = $this->getUserEntity($preparedType['pluralized'], $workshop);
        $this->AppFlash->setFlashMessage(str_replace('%name%', $userEntity->name, $preparedType['resignMessage']));
        $this->redirect($this->referer());
    }

    /**
     * @return $workshop
     */
    public function userDelete($type)
    {
        $preparedType = $this->checkType($type);
        $workshop = $this->prepareUserWorkshopActions();
        $query = 'DELETE FROM '.$preparedType['pluralized'] . '_workshops'.' WHERE workshop_uid = :workshopUid AND ' . $type . '_uid = :typeUid;';
        $params = [
            'workshopUid' => $workshop->uid,
            'typeUid' => $workshop->users[0]->uid
        ];
        $this->connection->execute($query, $params);
        return $workshop;
    }

    private function getUserEntity($type, $workshop)
    {
        if ($type == 'users') {
            return $workshop->$type[0];
        }
    }

    public function apply($relationModel, $relationTable, $foreignKey, $model, $userUid, $filterCondition)
    {
        if (! empty($this->request->getData())) {

            $workshopUid = $this->request->getData($relationTable.'.workshop_uid');

            $query = 'REPLACE INTO ' . $relationTable . ' (' . $foreignKey . ', workshop_uid, created) VALUES(' . $userUid . ', ' . $workshopUid . ', NOW());';
            $this->connection->execute($query);

            // immediately approve relation, if done by admin
            if ($this->AppAuth->isAdmin()) {
                $query = 'UPDATE ' . $relationTable . ' SET approved = NOW() WHERE workshop_uid = :workshopUid AND user_uid = :userUid';
                $params = [
                  'workshopUid' => $workshopUid,
                  'userUid' => $userUid
                ];
                $this->connection->execute($query, $params);
            }

            $userModel = Inflector::pluralize($model);
            $this->set('userModel', $userModel);
            $um = $this->getTableLocator()->get($userModel);
            $subject = 'Anfrage zur Mitarbeit bei deiner ' . Configure::read('AppConfig.initiativeNameSingular');
            $user = $um->find('all', [
                'conditions' => [
                    $userModel . '.uid' => $userUid,
                    $userModel . '.status > ' => APP_DELETED
                ]
            ])->first();

            /* START email-versand an alle initiativen-orgas */
            if (!$this->AppAuth->isAdmin()) {
                $this->Workshop = $this->getTableLocator()->get('Workshops');
                $workshop = $this->Workshop->find('all', [
                    'conditions' => [
                        'Workshops.uid' => $workshopUid,
                        'Workshops.status > ' => APP_DELETED
                    ],
                    'contain' => [
                        'Users',
                        'Users.Groups'
                    ]
                ])->first();

                foreach($workshop->users as $user) {
                    $user->revertPrivatizeData();
                }

                $email = new Mailer('default');
                $email->viewBuilder()->setTemplate('workshop_application');
                $email->setSubject($subject)
                    ->setViewVars([
                    'user' => ($user->user ? $user->user: $user),
                    'userModel' => $userModel,
                    'workshop' => $workshop
                ]);

                $orgaTeam = $this->Workshop->getOrgaTeam($workshop);
                if (!empty($orgaTeam)) {
                    foreach($orgaTeam as $orgaUser) {
                        $email->addTo($orgaUser->email);
                    }
                } else {
                    $email->addTo(Configure::read('AppConfig.notificationMailAddress'));
                }
                $email->send();
            }
            /* END email-versand an alle orgas */

            $message = 'Deine Anfrage wurde gestellt. Bitte warte, bis sie vom Verantwortlichen der Initiative bestätigt wird. Er wurde per E-Mail benachrichtigt.';
            if ($this->AppAuth->isAdmin()) {
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

        if (!$this->AppAuth->isAdmin()) {
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

    public function applyAsUser()
    {

        $metaTags = [
            'title' => 'Mitmachen'
        ];
        $this->set('metaTags', $metaTags);
        $filterCondition = [
            'UsersWorkshops.user_uid' => $this->AppAuth->getUserUid()
        ];

        // admins can apply in the name of another user
        $userUid = $this->AppAuth->getUserUid();
        if ($this->AppAuth->isAdmin() && $this->request->getData('users_workshops.user_uid') > 0) {
            $userUid = $this->request->getData('users_workshops.user_uid');
        }

        $this->associationTable = $this->getTableLocator()->get('UsersWorkshops');
        $this->apply('UsersWorkshops', 'users_workshops', 'user_uid', 'Users', $userUid, $filterCondition);

        if ($this->AppAuth->isAdmin()) {
            $this->User = $this->getTableLocator()->get('Users');
            $this->set('usersForDropdown', $this->User->getForDropdown());
        }

    }

    public function delete($workshopUid)
    {
        if (!($this->AppAuth->isOrga())) {
            throw new NotFoundException('orga role required');
        }

        $userTable = $this->getTableLocator()->get('Users');
        $user = $userTable->find('all', [
            'conditions' => [
                'Users.uid' => $this->AppAuth->getUserUid()
            ],
            'contain' => [
                'Workshops' => [
                    'fields' => [
                        'UsersWorkshops.user_uid'
                    ]
                ]
            ]
        ]);

        $workshopTable = $this->getTableLocator()->get('Workshops');
        $workshop = $workshopTable->find('all', [
            'conditions' => [
                'Workshops.uid' => $workshopUid,
                'Workshops.status > ' => APP_DELETED
            ],
            'contain' => [
                'Users',
                'Users.Groups'
            ]
        ])->first();

        foreach($workshop->users as $workshopUser) {
            $workshopUser->revertPrivatizeData();
        }

        if (!($this->AppAuth->isAdmin() || $this->Workshop->isUserInOrgaTeam($user->first(), $workshop))) {
            throw new NotFoundException('user ' . $this->AppAuth->getUserUid() .  ' ist not associated with workshop ' . $workshopUid);
        }

        // deleting workshop
        $entity = $this->Workshop->patchEntity($workshop, ['status' => APP_DELETED]);
        $this->Workshop->save($entity);

        // send email to orga users and admin
        $email = new Mailer('default');
        $email->viewBuilder()->setTemplate('workshop_deleted');
        $email->setSubject(Configure::read('AppConfig.initiativeNameSingular') . ' "'.$workshop->name.'" erfolgreich gelöscht')
            ->setViewVars([
                'workshop' => $workshop,
                'username' => $this->AppAuth->getUserName()
            ]);

        $orgaTeam = $this->Workshop->getOrgaTeam($workshop);
        foreach($orgaTeam as $orgaUser) {
            $email->addCc($orgaUser->email);
        }
        $email->setTo(Configure::read('AppConfig.notificationMailAddress'));
        $email->send();

        $message = 'Die Initiative wurde erfolgreich gelöscht und alle Organisatoren wurden per E-Mail informiert.';
        $this->AppFlash->setFlashMessage($message);

        $this->redirect($this->referer());

    }

    public function verwalten()
    {
        $this->Workshop = $this->getTableLocator()->get('Workshops');
        // complicated is-user-orga-check no needed again because this page is only accessible for orga users
        if ($this->AppAuth->isAdmin()) {
            $workshops = $this->Workshop->getWorkshopsForAdmin(APP_DELETED);
        } else {
            $workshops = $this->Workshop->getWorkshopsForAssociatedUser($this->AppAuth->getUserUid(), APP_DELETED);
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

    public function ajaxGetWorkshopDetail($workshopUid) {

        if (!$this->request->is('ajax')) {
            throw new ForbiddenException();
        }

        $this->RequestHandler->renderAs($this, 'json');

        $this->Workshop = $this->getTableLocator()->get('Workshops');

        $workshop = $this->Workshop->find('all', [
            'conditions' => [
                'Workshops.uid' => $workshopUid,
                'Workshops.status > ' . APP_DELETED
            ],
            'contain' => [
                'Countries',
                'Categories'
            ]
        ])->first();
        $this->set([
            'status' => 0,
            'message' => 'ok',
            'workshop' => $workshop,
        ]);
        $this->viewBuilder()->setOption('serialize', ['status', 'message', 'workshop']);

    }

    public function all() {

        $metaTags = [
            'title' => 'Suche ' . Configure::read('AppConfig.initiativeNamePlural') . ' in deiner Nähe',
            'description' => Configure::read('AppConfig.initiativeNamePlural'). ', Repair Cafés und andere Reparaturprojekte in der Umgebung suchen',
            'keywords' => Configure::read('AppConfig.platformName') . ', repair café, reparieren, Reparatur, repair, reparaturcafé, reparatur-café, repair-café, repaircafé'
        ];
        $this->set('metaTags', $metaTags);

        $conditions = [
            'Workshops.status' => APP_ON
        ];

        $query = $this->Workshop->find('all', [
            'conditions' => $conditions,
            'contain' => [
                'Countries',
                'Events'
            ]
        ]);

        $keyword = '';
        if (!empty($this->request->getQuery('keyword'))) {
            $keyword = h(strtolower(trim($this->request->getQuery('keyword'))));
            $query->where($this->Workshop->getKeywordSearchConditions($keyword, false));
        }
        $this->set('keyword', $keyword);

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
