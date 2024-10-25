<?php
namespace App\Controller;

use Cake\Core\Configure;
use Cake\Datasource\FactoryLocator;
use Cake\Event\EventInterface;
use Cake\Http\Exception\NotFoundException;
use Eluceo\iCal\Domain\Entity\Calendar;
use Eluceo\iCal\Domain\Entity\Event;
use Eluceo\iCal\Domain\Entity\TimeZone;
use \DateTimeZone as PhpDateTimeZone;
use \DateTime as PhpDateTime;
use \DateTimeImmutable as PhpDateTimeImmutable;
use Eluceo\iCal\Domain\ValueObject\DateTime as iCalDateTime;
use Eluceo\iCal\Domain\ValueObject\GeographicPosition;
use Eluceo\iCal\Domain\ValueObject\Location;
use Eluceo\iCal\Domain\ValueObject\TimeSpan;
use Eluceo\iCal\Presentation\Factory\CalendarFactory;
use InvalidArgumentException;
use Cake\View\JsonView;
use Cake\I18n\DateTime;
use App\Model\Entity\Worknews;
use App\Mailer\AppMailer;
use Cake\Database\Query;

class EventsController extends AppController
{

    public $Category;
    public $Event;
    public $Worknews;

    public function beforeFilter(EventInterface $event) {

        parent::beforeFilter($event);
        $this->Event = $this->getTableLocator()->get('Events');
        $this->Authentication->allowUnauthenticated([
            'detail',
            'all',
            'ajaxGetAllEventsForMap',
            'feed',
            'ical',
        ]);

    }

    public function initialize(): void
    {
        parent::initialize();
        $this->addViewClasses([JsonView::class]);

        $this->paginate = [
            'limit' => 1000,
            'maxLimit' => 1000,
        ];
    
    }

    public function ical()
    {

        if ($this->request->getParam('_ext') != 'ics') {
            throw new NotFoundException();
        }

        $this->disableAutoRender();

        $filename = 'events';
        $conditions = $this->Event->getListConditions();
        if ($this->request->getParam('uid') > 0) {
            $conditions['Workshops.uid'] = $this->request->getParam('uid');
            $filename = $this->request->getParam('uid');
        }
        $filename .= '.' . $this->request->getParam('_ext');

        $eventTable = FactoryLocator::get('Table')->get('Events');
        $events = $eventTable->find('all',
            conditions: $conditions,
            contain: [
                'Workshops',
                'Categories',
            ],
            order: $this->Event->getListOrder(),
        );

        $icalEvents = [];

        foreach($events as $event) {

            $icalEvent = new Event();

            $location = $event->strasse . ' ' . $event->zip . ' ' . $event->ort;
            if ($event->veranstaltungsort != '') {
                $location .= ' ' . $event->veranstaltungsort;
            }

            $description = $event->eventbeschreibung;
            if (!empty($event->categories)) {
                $description .= LF;
                $description .= 'Kategorien:';
                foreach($event->categories as $category) {
                    $description .= ' ' . $category->name;
                }
            }
            $description .= LF;
            $description .= Configure::read('AppConfig.serverName') . Configure::read('AppConfig.htmlHelper')->urlEventDetail($event->workshop->url, $event->uid, $event->datumstart);

            $location = new Location(str_replace('"', "'", $location));
            $location = $location->withGeographicPosition(new GeographicPosition($event->lat, $event->lng));

            $start = new PhpDateTime(
                $event->datumstart->i18nFormat(
                    Configure::read('DateFormat.Database')
                ) . ' ' .
                $event->uhrzeitstart->i18nFormat(
                    Configure::read('DateFormat.de.TimeWithSeconds')
                )
            );
            $start = new iCalDateTime(PhpDateTimeImmutable::createFromFormat('Y-m-d H:i:s', $start->format('Y-m-d H:i:s')), false);

            $end = new PhpDateTime(
                $event->datumstart->i18nFormat(
                    Configure::read('DateFormat.Database')
                ) . ' ' .
                $event->uhrzeitend->i18nFormat(
                    Configure::read('DateFormat.de.TimeWithSeconds')
                )
            );
            $end = new iCalDateTime(PhpDateTimeImmutable::createFromFormat('Y-m-d H:i:s', $end->format('Y-m-d H:i:s')), false);
            $occurrence = new TimeSpan($start, $end);

            $icalEvent
                ->setSummary(str_replace('"', "'", $event->workshop->name))
                ->setDescription(str_replace('"', "'", strip_tags($description)))
                ->setOccurrence($occurrence)
                ->setLocation($location);

            $icalEvents[] = $icalEvent;
        }

        $icalCalendar = new Calendar($icalEvents);

        $phpDateTimeZone = new PhpDateTimeZone(Configure::read('App.defaultTimezone'));
        $timeZone = TimeZone::createFromPhpDateTimeZone(
            $phpDateTimeZone,
            new PhpDateTimeImmutable('2010-01-01 00:00:00', $phpDateTimeZone),
            new PhpDateTimeImmutable('2050-01-01 00:00:00', $phpDateTimeZone),
        );
        $icalCalendar->addTimeZone($timeZone);

        $componentFactory = new CalendarFactory();
        $calendarComponent = $componentFactory->createCalendar($icalCalendar);

        $this->response = $this->response->withHeader('Content-type', 'text/calendar; charset=utf-8');
        $this->response = $this->response->withHeader('Content-Disposition', 'attachment; filename="'.$filename.'"');
        $this->response = $this->response->withStringBody($calendarComponent->__toString());

        return $this->response;

    }

    public function myEvents()
    {

        $hasEditEventPermissions = $this->isAdmin() || $this->isOrga();

        $this->Workshop = $this->getTableLocator()->get('Workshops');
        if ($this->isAdmin()) {
            $workshops = $this->Workshop->getWorkshopsWithUsers(APP_DELETED);
        } else {
            $workshops = $this->Workshop->getWorkshopsForAssociatedUser($this->loggedUser->uid, APP_DELETED);
        }

        $workshops->contain([
            'Events.InfoSheets.OwnerUsers',
            'Events.InfoSheets.Brands' => function($q) {
                return $q->select($this->Workshop->Events->InfoSheets->Brands);
            },
            'Events.InfoSheets.Categories' => function($q) {
                return $q->select($this->Workshop->Events->InfoSheets->Categories);
            }
        ]);

        $this->Workshop->getAssociation('Events')->setConditions(['Events.status > ' . APP_DELETED])
        ->setSort([
            'Events.datumstart' => 'DESC',
            'Events.uhrzeitstart' => 'DESC'
        ]);

        $conditions = [
            'InfoSheets.status > ' . APP_DELETED
        ];

        // show only own content for repair helper
        if (!$hasEditEventPermissions) {
            $conditions['InfoSheets.owner'] = $this->isLoggedIn() ? $this->loggedUser->uid : 0;
        }

        $this->Workshop->getAssociation('Events')->getAssociation('InfoSheets')
        ->setConditions($conditions)
        ->setSort([
            'InfoSheets.device_name' => 'ASC'
        ]);

        $workshops = $this->paginate($workshops, [
            'limit' => 100,
        ]);

        $worknewsTable = FactoryLocator::get('Table')->get('Worknews');
        foreach($workshops as $workshop) {
            $workshop->infoSheetCount = 0;
            $workshop->worknewsCount = $worknewsTable->getSubscribers($workshop->uid)->count();
            if (!empty($workshop->events)) {
                foreach($workshop->events as $event) {
                    $workshop->infoSheetCount += count($event->info_sheets);
                }
            }
        }

        // workaround: if limit is not added, this warning appears
        // Warning (2): count(): Parameter must be an array or an object that implements Countable in [src/Template/Events/my_events.ctp, line 42]
        $this->set('workshops', $workshops);

        $metaTags = [
            'title' => 'Meine Termine'
        ];
        $this->set('metaTags', $metaTags);

        $this->set('hasEditEventPermissions', $hasEditEventPermissions);
        $this->set('infoSheetColspan', $hasEditEventPermissions ? 11 : 8);

    }

    public function feed()
    {

        $this->Event = $this->getTableLocator()->get('Events');
        $events = $this->Event->find('all',
        conditions: $this->Event->getListConditions(),
        order: [
            'Events.datumstart' => 'ASC'
        ],
        contain: [
            'Workshops'
        ]);

        if ($events->count() == 0) {
            throw new NotFoundException('no events found');
        }

        $this->set('events', $events);

    }

    public function delete($eventUid)
    {
        if ($eventUid === null) {
            throw new NotFoundException;
        }

        $event = $this->Event->find('all',
            conditions: [
                'Events.uid' => $eventUid,
                'Events.status >= ' . APP_DELETED
            ],
            contain: [
                'Categories',
                'Workshops'
            ]
        )->first();

        if (empty($event)) {
            throw new NotFoundException;
        }

        // keep this line here!!!
        $originalEventStatus = $event->status;

        $patchedEntity = $this->Event->patchEntity(
            $this->Event->get($eventUid),
            ['status' => APP_DELETED]
        );

        if ($this->Event->save($patchedEntity)) {
            $this->AppFlash->setFlashMessage('Der Termin wurde erfolgreich gelöscht.');

            if ($originalEventStatus && !$event->datumstart->isPast()) {
                // START notify subscribers
                $this->Worknews = $this->getTableLocator()->get('Worknews');
                $subscribers = $this->Worknews->find('all', conditions: [
                    'Worknews.workshop_uid' => $event->workshop_uid,
                    'Worknews.confirm' => Worknews::STATUS_OK,
                ]);

                if (!empty($subscribers)) {
                    $email = new AppMailer();
                    $email->viewBuilder()->setTemplate('event_deleted');
                    foreach ($subscribers as $subscriber) {
                        $email->setTo($subscriber->email)
                        ->setSubject('Termin gelöscht')
                        ->setViewVars([
                            'unsub' => $subscriber->unsub,
                            'event' => $event,
                        ]);
                        $email->addToQueue();
                    }
                }
                // END notify subscribers
            }

        } else {
            $this->AppFlash->setErrorMessage('Beim Löschen ist ein Fehler aufgetreten');
        }

        $this->redirect($this->referer());

    }

    public function add($preselectedWorkshopUid)
    {

        $event = $this->Event->newEntity(
            [
                'status' => APP_ON,
                'workshop_uid' => $preselectedWorkshopUid
            ],
            ['validate' => false]
        );

        $this->set('metaTags', ['title' => 'Termin erstellen']);

        $this->Workshop = $this->getTableLocator()->get('Workshops');
        // complicated is-user-orga-check no needed again because this page is only accessible for orga users
        if ($this->isAdmin()) {
            $workshops = $this->Workshop->getWorkshopsWithUsers(APP_DELETED);
        } else {
            $workshops = $this->Workshop->getWorkshopsForAssociatedUser($this->isLoggedIn() ? $this->loggedUser->uid : 0, APP_DELETED);
        }

        $this->set('workshopsForDropdown', $this->Workshop->transformForDropdown($workshops));
        $this->set('preselectedWorkshopUid', $preselectedWorkshopUid);
        $this->set('editFormUrl', Configure::read('AppConfig.htmlHelper')->urlEventNew($preselectedWorkshopUid));
        $this->set('isDuplicateMode', false);

        $this->_edit([$event], false);

        // assures rendering of success message on redirected page and NOT before and then not showing it
        if (empty($this->request->getData())) {
            $this->render('edit');
        }
    }

    public function duplicate($eventUid) {
        $event = $this->Event->find('all',
            conditions: [
                'Events.uid' => $eventUid,
                'Events.status >= ' . APP_DELETED
            ],
            contain: [
                'Categories',
                'Workshops'
            ]
        )->first();

        if (empty($event)) {
            throw new NotFoundException;
        }
        $this->setIsCurrentlyUpdated($event->uid);
        $this->set('metaTags', ['title' => 'Termin duplizieren']);
        $this->set('editFormUrl', Configure::read('AppConfig.htmlHelper')->urlEventNew($event->workshop_uid));
        $this->set('preselectedWorkshopUid', $event->workshop_uid);
        $this->set('isDuplicateMode', true);
        $this->_edit([$event], false);
        $this->render('edit');
    }

    public function edit($eventUid)
    {

        if ($eventUid === null) {
            throw new NotFoundException;
        }

        $event = $this->Event->find('all',
            conditions: [
                'Events.uid' => $eventUid,
                'Events.status >= ' . APP_DELETED,
            ],
            contain: [
                'Categories',
                'Workshops',
            ]
        )->first();

        if (empty($event)) {
            throw new NotFoundException;
        }

        $this->setIsCurrentlyUpdated($event->uid);
        $this->set('metaTags', ['title' => 'Termin bearbeiten']);
        $this->set('editFormUrl', Configure::read('AppConfig.htmlHelper')->urlEventEdit($event->uid));
        $this->set('isDuplicateMode', false);

        $worknewsTable = FactoryLocator::get('Table')->get('Worknews');
        $this->set('worknewsCount', $worknewsTable->getSubscribers($event->workshop_uid)->count());

        $eventStartInLessThan7Days = $event->datumstart->isWithinNext('7 days');
        $this->set('eventStartInLessThan7Days', $eventStartInLessThan7Days);
        
        $patchedEntities = $this->_edit([$event], true);
        $patchedEntity = $patchedEntities['events'][0];

        // never send notification mail on add
        // if event is edited and renotify is active, send mail to subscriber
        if (!empty($event->workshop)) {
            $workshop = $event->workshop;
        }

        // notify subscribers
        if (isset($workshop) && $patchedEntity->renotify) {
            $this->Worknews = $this->getTableLocator()->get('Worknews');
            $subscribers = $this->Worknews->getSubscribers($patchedEntity->workshop_uid);
            if (!empty($subscribers)) {
                $this->Worknews->sendNotifications($subscribers, 'Termin geändert: ' . $workshop->name, 'event_changed', $workshop, $patchedEntity, $patchedEntities['dirtyFields'], $patchedEntities['originalValues']);
            }
        }
    }

    private function _edit($events, $isEditMode)
    {
        $this->Category = $this->getTableLocator()->get('Categories');
        $this->set('categories', $this->Category->getForDropdown(APP_ON));

        $this->set('uid', $events[0]->uid);

        $this->setReferer();

        if (!empty($this->request->getData())) {
            $patchedEvents = [];
            foreach($this->request->getData() as $data) {
                if (!is_array($data)) {
                    continue; // skip referer
                }
                if (!array_key_exists('datumstart', $data)) {
                    continue; // skip metadata (fields / unlocked / debug)
                }
                $data = array_merge($this->request->getData()[0], $data);
                if ($data['datumstart']) {
                    $data['datumstart'] = new DateTime($data['datumstart']);
                }
                if ($data['uhrzeitstart']) {
                    $data['uhrzeitstart'] = new DateTime($data['uhrzeitstart']);
                }
                if ($data['uhrzeitend']) {
                    $data['uhrzeitend'] = new DateTime($data['uhrzeitend']);
                }
                if (!$data['use_custom_coordinates']) {
                    $addressString = $data['strasse'] . ', ' . $data['zip'] . ' ' . $data['ort'] . ', ' . $data['land'];
                    $geoData = $this->geoService->getGeoDataByAddress($addressString);
                    $data['lat'] = $geoData['lat'];
                    $data['lng'] = $geoData['lng'];
                }
                if (!empty($data['use_custom_coordinates'])) {
                    $geoData = $this->geoService->getGeoDataByCoordinates($data['lat'], $data['lng']);
                    $data['lat'] = str_replace(',', '.', $data['lat']);
                    $data['lng'] = str_replace(',', '.', $data['lng']);
                }
                $data['province_id'] = $geoData['provinceId'] ?? 0;
                if ($isEditMode) {
                    $data['uid'] = $events[0]->uid;
                }
                $event = clone($events[0]);
                $patchedEvents[] = $this->Event->patchEntity($event, $data);
            }

            $events = $patchedEvents;

            $hasErrors = false;
            foreach($events as $e) {
                if ($e->hasErrors()) {
                    $hasErrors |= true;
                }
            }

            $errors = $events[0]->getErrors();
            if (isset($errors['lat']) && isset($errors['lat']['geoCoordinatesInBoundingBox'])) {
                $this->AppFlash->setFlashError($errors['lat']['geoCoordinatesInBoundingBox']);
            }

            if (!$hasErrors) {
                $eventsTable = FactoryLocator::get('Table')->get('Events');
                if (isset($event)) {
                    $dirtyFields = $event->getDirty();
                    $originalValues = $event->getOriginalValues();
                }
                $eventsTable->getConnection()->transactional(function () use ($eventsTable, $events) {
                    foreach ($events as $e) {
                        $eventsTable->save($e, ['atomic' => true]);
                    }
                });

                $message = 'Termine';
                if (count($events) == 1) {
                    $message = 'Termin';
                }
                $message = count($events) . ' ' . $message . ' erfolgreich gespeichert.';
                $this->AppFlash->setFlashMessage($message);
                $this->redirect($this->getPreparedReferer());
                return [
                    'events' => $events,
                    'dirtyFields' => $dirtyFields ?? [],
                    'originalValues' => $originalValues ?? [],
                ];
            }

        }

        $this->set('events', $events);
        $this->set('isEditMode', $isEditMode);
        $this->render('edit');
        return [
            'events' => $events,
        ];


    }

    public function ajaxGetAllEventsForMap()
    {

        if (!$this->request->is('ajax')) {
            throw new NotFoundException();
        }

        $this->request = $this->request->withParam('_ext', 'json');

        $keyword = '';
        $conditions = $this->Event->getListConditions();

        $allParamsEmpty = empty($this->request->getQuery('keyword'));

        $events = $this->Event->find('all',
            conditions:  $conditions,
            fields:  $this->Event->getListFields(),
            order:  $this->Event->getListOrder(),
            contain:  [
                'Workshops',
                'Categories'
            ]
        );
        $events->distinct($this->Event->getListFields());

        if (!empty($this->request->getQuery('keyword'))) {
            $keyword = h(strtolower(trim($this->request->getQuery('keyword'))));
            if ($keyword !== '' && $keyword !== 'null') {
                $events->where($this->Event->getKeywordSearchConditions($keyword, false));
            }
        }

        if (! $allParamsEmpty) {
            $events->where($this->Event->getKeywordSearchConditions($keyword, true));
        }

        if (!empty($this->request->getQuery('categories'))) {
            $categories = explode(',', h($this->request->getQuery('categories')));
            if (!empty($categories)) {
                $events->notMatching('Categories', function(\Cake\ORM\Query $q) use ($categories) {
                    return $q->where([
                        'Categories.id IN' => $categories
                    ]);
                });
            }
        }
        $this->set([
            'status' => 1,
            'message' => 'ok',
            'events' => $this->combineEventsForMap($events)
        ]);
        $this->viewBuilder()->setOption('serialize', ['status', 'message', 'events']);
    }

    public function all()
    {

        $metaTags = Configure::read('AppConfig.metaTags.' . $this->request->getParam('controller') . '.' . $this->request->getParam('action'));
        $this->set('metaTags', $metaTags);

        // check if all categories are integers (sometimes bots call ?categories=87/ - with a trailing slash)
        if (!empty($this->request->getQuery('categories'))) {
            $categories = explode(',', h($this->request->getQuery('categories')));
            $categoriesAsIntegers = array_map('intval', $categories);
            if ($categories != $categoriesAsIntegers) {
                throw new InvalidArgumentException('categories must only contain integers');
            }
        }

        $conditions = $this->Event->getListConditions();

        // get count without any filters
        $eventsTable = FactoryLocator::get('Table')->get('Events');
        $allEventsCount = $eventsTable->find('all',
            conditions: $conditions,
            contain:  [
                'Workshops'
            ]
        )->count();
        $this->set('allEventsCount', $allEventsCount);

        $selectedCategories = !empty($this->request->getQuery('categories')) ? explode(',', h($this->request->getQuery('categories'))) : [];
        $this->set('selectedCategories', $selectedCategories);

        $isOnlineEvent = $this->request->getQuery('isOnlineEvent') ?? false;
        $isOnlineEvent = (bool) h($isOnlineEvent);
        $this->set('isOnlineEvent', $isOnlineEvent);

        $this->Category = $this->getTableLocator()->get('Categories');
        $categories = $this->Category->getMainCategoriesForFrontend();

        $preparedCategories = [];
        $categoryClass = '';
        foreach ($categories as $category) {

            // category is selected
            if (count($selectedCategories) > 0) {
                if (in_array($category->id, $selectedCategories)) {
                    $categoryClass = 'selected';
                    $categoryIdsForNewUrl = [];
                    foreach ($selectedCategories as $sc) {
                        if ($sc != $category->id) {
                            $categoryIdsForNewUrl[] = $sc;
                        }
                    }
                } else {
                    // category is not selected
                    $categoryClass = 'not-selected';
                    $categoryIdsForNewUrl = array_merge($selectedCategories, [
                        $category->id
                    ]);
                }
            }

            // initially all categories selected
            if (count($selectedCategories) == 0) {
                $categoryClass = 'selected';
                $categoryIdsForNewUrl = array_merge($selectedCategories, [
                    $category->id
                ]);
            }

            /* @phpstan-ignore-next-line */
            $newUrl = 'categories=' . join(',', $categoryIdsForNewUrl);
            $newUrl = str_replace('categories=,', '&categories=', $newUrl);

            if (empty($this->request->getQuery('keyword'))) {
                $newUrl = '?' . $newUrl;
            } else {
                $newUrl = '?keyword=' . h($this->request->getQuery('keyword')) . '&' . $newUrl;
            }

            if (!empty($this->request->getQuery('provinceId'))) {
                $newUrl = $newUrl . '&provinceId=' . h($this->request->getQuery('provinceId'));
            }
            if (!empty($this->request->getQuery('isOnlineEvent'))) {
                $newUrl = $newUrl . '&isOnlineEvent=' . h($this->request->getQuery('isOnlineEvent'));
            }

            $newUrl = str_replace('//', '/', $newUrl);

            $category['href'] = $newUrl;
            $category['class'] = $categoryClass;
            $preparedCategories[] = [
                'id' => $category->id,
                'name' => $category->name,
                'icon' => $category->icon,
                'href' => $newUrl,
                'class' => $categoryClass
            ];
        }
        $this->set('preparedCategories', $preparedCategories);

        $query = $eventsTable->find('all',
            conditions: $conditions,
        );
        
        $keyword = '';
        if (!empty($this->request->getQuery('keyword'))) {
            $keyword = h(strtolower(trim($this->request->getQuery('keyword'))));
            $query->where($this->Event->getKeywordSearchConditions($keyword, false));
        }
        $this->set('keyword', $keyword);

        $resetCategoriesUrl = '/termine';
        if ($keyword != '') {
            $resetCategoriesUrl = '/termine?keyword=' . $keyword;
        }
        if (!empty($this->request->getQuery('provinceId'))) {
            $queryStringStartsWith = '?';
            if ($keyword != '') {
                $queryStringStartsWith = '&';
            }
            $resetCategoriesUrl .= $queryStringStartsWith . 'provinceId='.$this->request->getQuery('provinceId');
        }
        $this->set('resetCategoriesUrl', $resetCategoriesUrl);

        $provincesTable = $this->getTableLocator()->get('Provinces');
        $provinceCounts = $eventsTable->getProvinceCounts();
        $provinces = $provincesTable->getForDropdown($provinceCounts);
        $this->set('provinces', $provinces);

        $provinceId = $this->request->getQuery('provinceId', 0);
        $this->set('provinceId', $provinceId);

        if ($provinceId > 0) {
            $query->where([$eventsTable->aliasField('province_id') => $provinceId]);
        }


        if (!empty($this->request->getQuery('categories'))) {
            $categories = explode(',', h($this->request->getQuery('categories')));
            if (!empty($categories)) {
                $query->matching('Categories', function(Query $q) use ($categories) {
                    return $q->where([
                        'Categories.id IN' => $categories
                    ]);
                });
            }
        }

        if ($isOnlineEvent) {
            $query->where(['Events.is_online_event' => 1]);
        }

        $query->distinct($eventsTable->getListFields());
        $query->select($eventsTable->getListFields());
        $query->orderBy($eventsTable->getListOrder());
        $query->contain([
            'Workshops',
            'Categories'
        ]);
        $events = $this->paginate($query);

        $this->set('events', $events);

        // $events needs to be cloned, because unset($e['workshop']); in combineEventsForMap would also remove it from $events
        // $events cannot be cloned because it is a resultset
        // so call $this->pagniate twice - no performance problem!
        $newEvents = $this->paginate($query);
        $eventsForMap = $this->combineEventsForMap($newEvents);
        $this->set('eventsForMap', $eventsForMap);

        $urlOptions = [
            'url' => [
                '?' => [
                    'keyword' => $this->request->getQuery('keyword'),
                    'provinceId' => $this->request->getQuery('provinceId'),
                    'categories' => $this->request->getQuery('categories'),
                    'isOnlineEvent' => $this->request->getQuery('isOnlineEvent'),
                ],
            ],
        ];
        $this->set('urlOptions', $urlOptions);

    }

    /**
     * combines multiple events to one marker
     *
     * @param array $events
     * @return array
     */
    private function combineEventsForMap($events)
    {
        $eventsForMap1 = [];

        foreach ($events as $event) {
            unset($event->uhrzeitstart);
            unset($event->uhrzeitend);
            $preparedWorkshop = [];
            if ($event->workshop) {
                $tmpWorkshop = $event->workshop;
                $preparedWorkshop['name'] = $tmpWorkshop->name;
                $preparedWorkshop['image'] = $tmpWorkshop->image;
                $preparedWorkshop['url'] = $tmpWorkshop->url;
            }
            $eventsForMap1[$event->uniquePlace]['Event'] = $event;
            $eventsForMap1[$event->uniquePlace]['Events'][] = $event;
            $eventsForMap1[$event->uniquePlace]['Workshop'] = $preparedWorkshop;
        }
        $eventsForMap = [];
        foreach ($eventsForMap1 as $event) {
            $preparedEvent = [
                'Event' => $event['Event'],
                'Workshop' => $event['Workshop'],
                'Events' => []
            ];
            foreach ($event['Events'] as $e) {
                unset($e['workshop']);
                $preparedEvent['Events'][] = $e;
            }
            $eventsForMap[] = $preparedEvent;
        }
        return $eventsForMap;
    }

}
?>
