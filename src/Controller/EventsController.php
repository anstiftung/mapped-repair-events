<?php
namespace App\Controller;

use Cake\Core\Configure;
use Cake\Event\EventInterface;
use Cake\I18n\FrozenTime;
use Cake\Mailer\Mailer;
use Cake\Http\Exception\NotFoundException;
use Eluceo\iCal\Component\Calendar;
use Eluceo\iCal\Component\Event;
use Eluceo\iCal\Property\Event\Geo;

class EventsController extends AppController
{

    public function beforeFilter(EventInterface $event) {

        parent::beforeFilter($event);
        $this->Event = $this->getTableLocator()->get('Events');
        $this->AppAuth->allow([
            'detail',
            'all',
            'ajaxGetAllEventsForMap',
            'feed',
            'ical',
        ]);
    }

    public function isAuthorized($user)
    {

        if ($this->request->getParam('action') == 'myEvents') {
            return $this->AppAuth->user();
        }

        if ($this->request->getParam('action') == 'add') {

            if ($this->AppAuth->isAdmin()) {
                $this->useDefaultValidation = false;
                return true;
            }

            // repair helpers are not allowed to add events
            if (!$this->AppAuth->isOrga()) {
                return false;
            }

            $workshopUid = (int) $this->request->getParam('pass')[0];
            $this->Workshop = $this->getTableLocator()->get('Workshops');
            $workshop = $this->Workshop->getWorkshopForIsUserInOrgaTeamCheck($workshopUid);
            if ($this->Workshop->isUserInOrgaTeam($this->AppAuth->user(), $workshop)) {
                return true;
            }

        }

        if (in_array($this->request->getParam('action'), ['edit', 'delete', 'duplicate'])) {

            // repair helpers are not allowed to edit, delete or duplicate events (even not own content - which does not exist because "add" is locked for repairhelpers too)
            if (!($this->AppAuth->isOrga() || $this->AppAuth->isAdmin())) {
                return false;
            }

            $eventUid = (int) $this->request->getParam('pass')[0];

            $this->Event = $this->getTableLocator()->get('Events');
            $event = $this->Event->find('all', [
                'conditions' => [
                    'Events.uid' => $eventUid,
                    'Events.status > ' . APP_DELETED
                ]
            ])->first();
            $workshopUid = $event->workshop_uid;

            if ($this->request->getParam('action') == 'edit' && $event->datumstart->isPast()) {
                return false;
            }

            if ($this->AppAuth->isAdmin()) {
                $this->useDefaultValidation = false;
                return true;
            }

            // all approved orgas are allowed to edit their events
            $this->Workshop = $this->getTableLocator()->get('Workshops');
            $workshop = $this->Workshop->getWorkshopForIsUserInOrgaTeamCheck($workshopUid);
            if ($this->Workshop->isUserInOrgaTeam($this->AppAuth->user(), $workshop)) {
                return true;
            }

            return false;
        }

        return parent::isAuthorized($user);

    }

    public function ical()
    {

        if ($this->request->getParam('_ext') != 'ics') {
            throw new NotFoundException();
        }

        $this->disableAutoRender();
        $icalCalendar = new Calendar('www.reparatur-initiativen.de');

        $tz = Configure::read('App.defaultTimezone');
        $dtz = new \DateTimeZone($tz);
        date_default_timezone_set($tz);

        $vTimezoneRuleDst = new \Eluceo\iCal\Component\TimezoneRule(\Eluceo\iCal\Component\TimezoneRule::TYPE_DAYLIGHT);
        $vTimezoneRuleDst->setTzName($tz);
        $vTimezoneRuleDst->setDtStart(new \DateTime('1981-03-29 02:00:00', $dtz));
        $vTimezoneRuleDst->setTzOffsetFrom('+0100');
        $vTimezoneRuleDst->setTzOffsetTo('+0200');

        $vTimezone = new \Eluceo\iCal\Component\Timezone($tz);
        $vTimezone->addComponent($vTimezoneRuleDst);

        $icalCalendar->setTimezone($vTimezone);

        $events = $this->Events->find('all', [
            'conditions' => $this->Event->getListConditions(),
            'contain' => [
                'Workshops',
                'Categories',
            ]
        ]);

        foreach($events as $event) {

            $icalEvent = new Event();

            $location = $event->strasse . ' ' . $event->zip . ' ' . $event->ort;
            if ($event->veranstaltungsort != '') {
                $location .= ' ' . $event->veranstaltungsort;
            }

            $description = $event->eventbeschreibung;
            if (!empty($event->categories)) {
                $description .= LF;
                foreach($event->categories as $category) {
                    $description .= ' ' . $category->name;
                }
            }
            $description .= LF;
            $description .= Configure::read('AppConfig.serverName') . Configure::read('AppConfig.htmlHelper')->urlEventDetail($event->workshop->url, $event->uid, $event->datumstart);

            $icalEvent
                ->setSummary(str_replace('Reparatur-Termin ' . $event->workshop->name, '\"', "'"))
                ->setUseTimezone(true)
                ->setDescription(str_replace($description, '\"', "'"))
                ->setDtStart(new \DateTime($event->uhrzeitstart->i18nFormat(Configure::read('DateFormat.DatabaseWithTime'))))
                ->setDtEnd(new \DateTime($event->uhrzeitend->i18nFormat(Configure::read('DateFormat.DatabaseWithTime'))))
                ->setLocation(str_replace($location, '\"', "'"), '', new Geo($event->lat, $event->lng));

            if ($event->uhrzeitstart_formatted == '00:00' && $event->uhrzeitend_formatted == '00:00') {
                $icalEvent->setNoTime(true);
            }

            $icalCalendar->addComponent($icalEvent);
        }

        $this->response = $this->response->withHeader('Content-type', 'text/calendar');
        $this->response = $this->response->withHeader('Content-Disposition', 'text/calendar');
        $this->response = $this->response->withHeader('Content-Disposition', 'attachment; filename="events.ics"');
        $this->response = $this->response->withStringBody($icalCalendar->render());

        return $this->response;

    }

    public function myEvents()
    {

        $hasEditEventPermissions = $this->AppAuth->isAdmin() || $this->AppAuth->isOrga();

        $this->Workshop = $this->getTableLocator()->get('Workshops');
        // complicated is-user-orga-check no needed again because this page is only accessible for orga users
        if ($this->AppAuth->isAdmin()) {
            $workshops = $this->Workshop->getWorkshopsForAdmin(APP_DELETED);
        } else {
            $workshops = $this->Workshop->getWorkshopsForAssociatedUser($this->AppAuth->getUserUid(), APP_DELETED);
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
            $conditions['InfoSheets.owner'] = $this->AppAuth->getUserUid();
        }

        $this->Workshop->getAssociation('Events')->getAssociation('InfoSheets')
        ->setConditions($conditions)
        ->setSort([
            'InfoSheets.device_name' => 'ASC'
        ]);
        $workshops->limit(1000000);

        foreach($workshops as $workshop) {
            $workshop->infoSheetCount = 0;
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

        if (! $this->RequestHandler->prefers('rss')) {
            throw new NotFoundException('kein rss');
        }

        $this->Event = $this->getTableLocator()->get('Events');
        $events = $this->Event->find('all', [
            'conditions' => $this->Event->getListConditions(),
            'order' => [
                'Events.datumstart' => 'ASC'
            ],
            'contain' => [
                'Workshops'
            ]
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

        $event = $this->Event->find('all', [
            'conditions' => [
                'Events.uid' => $eventUid,
                'Events.status >= ' . APP_DELETED
            ],
            'contain' => [
                'Categories',
                'Workshops'
            ]
        ])->first();

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

            if ($originalEventStatus) {
                // START notify subscribers
                $this->Worknews = $this->getTableLocator()->get('Worknews');
                $subscribers = $this->Worknews->find('all', [
                    'conditions' => [
                        'Worknews.workshop_uid' => $event->workshop_uid,
                        'Worknews.confirm' => 'ok'
                    ]
                ]);

                if (!empty($subscribers)) {
                    $email = new Mailer('default');
                    $email->viewBuilder()->setTemplate('event_deleted');
                    foreach ($subscribers as $subscriber) {
                        $email->setTo($subscriber->email)
                        ->setSubject('Termin gelöscht')
                        ->setViewVars([
                            'url' => Configure::read('AppConfig.htmlHelper')->urlWorkshopDetail($event->workshop->url),
                            'unsub' => $subscriber->unsub
                        ]);
                        $email->send();
                    }
                }
                // END notify subscribers
            }

        } else {
            $this->AppFlash->setErrorMessage('Beim Löschen ist ein Fehler aufgetreten');
        }

        $this->redirect($this->request->referer(false));

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
        if ($this->AppAuth->isAdmin()) {
            $workshops = $this->Workshop->getWorkshopsForAdmin(APP_DELETED);
        } else {
            $workshops = $this->Workshop->getWorkshopsForAssociatedUser($this->AppAuth->getUserUid(), APP_DELETED);
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
        $event = $this->Event->find('all', [
            'conditions' => [
                'Events.uid' => $eventUid,
                'Events.status >= ' . APP_DELETED
            ],
            'contain' => [
                'Categories',
                'Workshops'
            ]
        ])->first();

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

        $event = $this->Event->find('all', [
            'conditions' => [
                'Events.uid' => $eventUid,
                'Events.status >= ' . APP_DELETED
            ],
            'contain' => [
                'Categories',
                'Workshops'
            ]
        ])->first();

        if (empty($event)) {
            throw new NotFoundException;
        }

        $this->setIsCurrentlyUpdated($event->uid);
        $this->set('metaTags', ['title' => 'Termin bearbeiten']);
        $this->set('editFormUrl', Configure::read('AppConfig.htmlHelper')->urlEventEdit($event->uid));
        $this->set('isDuplicateMode', false);
        $patchedEntities = $this->_edit([$event], true);

        $patchedEntity = $patchedEntities[0];

        // only send notfications if status was changed from off to on
        $sendNotificationMails = $event->status == APP_OFF && $patchedEntity->status == APP_ON;

        // never send notification mail on add! this is done in cronjob SendWorknewsNotificationShell
        // if event is edited and renotify is active, send mail to subscriber
        if (!empty($event->workshop)) {
            $workshop = $event->workshop;
            $sendNotificationMails |= $patchedEntity->renotify;
        }

        // notify subscribers
        if (isset($workshop) && $sendNotificationMails) {
            $this->Worknews = $this->getTableLocator()->get('Worknews');
            $subscribers = $this->Worknews->getSubscribers($patchedEntity->workshop_uid);
            if (!empty($subscribers)) {
                $this->Worknews->sendNotifications($subscribers, 'Termin geändert: ' . $workshop->name, 'event_changed', $workshop, $patchedEntity);
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
            $i = 0;
            $patchedEvents = [];
            foreach($this->request->getData() as $data) {
                if (!is_array($data)) {
                    continue; // skip referer
                }
                $data = array_merge($this->request->getData()[0], $data);
                if ($data['datumstart']) {
                    $data['datumstart'] = new FrozenTime($data['datumstart']);
                }
                if ($data['uhrzeitstart']) {
                    $data['uhrzeitstart'] = new FrozenTime($data['uhrzeitstart']);
                }
                if ($data['uhrzeitend']) {
                    $data['uhrzeitend'] = new FrozenTime($data['uhrzeitend']);
                }
                if (!$data['use_custom_coordinates']) {
                    $addressString = $data['strasse'] . ', ' . $data['zip'] . ' ' . $data['ort'] . ', ' . $data['land'];
                    $coordinates = $this->getLatLngFromGeoCodingService($addressString);
                    $data['lat'] = $coordinates['lat'];
                    $data['lng'] = $coordinates['lng'];
                }
                if (!empty($data['use_custom_coordinates'])) {
                    $data['lat'] = str_replace(',', '.', $data['lat']);
                    $data['lng'] = str_replace(',', '.', $data['lng']);
                }
                if ($isEditMode) {
                    $data['uid'] = $events[0]->uid;
                }
                $event = clone($events[0]);
                $patchedEvents[] = $this->Event->patchEntity($event, $data);
                $i++;
            }

            $events = $patchedEvents;

            $hasErrors = false;
            foreach($events as $event) {
                if ($event->hasErrors()) {
                    $hasErrors |= true;
                }
            }

            $errors = $events[0]->getErrors();
            if (isset($errors['lat']) && isset($errors['lat']['numeric'])) {
                $this->AppFlash->setFlashError($errors['lat']['numeric']);
            }

            if (!$hasErrors) {
                $eventModel = $this->Event;
                $this->Event->getConnection()->transactional(function () use ($eventModel, $events) {
                    foreach ($events as $event) {
                        $eventModel->save($event, ['atomic' => false]);
                    }
                });
                $message = 'Termine';
                if (count($events) == 1) {
                    $message = 'Termin';
                }
                $message = count($events) . ' ' . $message . ' erfolgreich gespeichert.';
                $this->AppFlash->setFlashMessage($message);
                $this->redirect($this->request->getData()['referer']);
                return $events;
            }

        }

        $this->set('events', $events);
        $this->set('isEditMode', $isEditMode);
        $this->render('edit');
        return $events;

    }

    public function ajaxGetAllEventsForMap()
    {

        if (!$this->request->is('ajax')) {
            throw new NotFoundException();
        }

        $this->RequestHandler->renderAs($this, 'json');

        $keyword = '';
        $conditions = $this->Event->getListConditions();

        $allParamsEmpty = empty($this->request->getQuery('keyword'));

        $events = $this->Event->find('all', [
            'conditions' => $conditions,
            'fields' => $this->Event->getListFields(),
            'order' => $this->Event->getListOrder(),
            'contain' => [
                'Workshops',
                'Categories'
            ]
        ]);
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

        $metaTags = [
            'title' => 'Suche Reparaturtermine in deiner Nähe',
            'description' => 'Termine und Veranstaltungen von Repair Cafés und anderen ' . Configure::read('AppConfig.initiativeNamePlural') . ' in deiner Nähe',
            'keywords' => 'repair café, repair cafe, reparieren, repair, reparatur, reparatur-initiativen, netzwerk reparatur-initiativen, reparaturtermin, reparaturveranstaltung'
        ];
        $this->set('metaTags', $metaTags);

        $conditions = $this->Event->getListConditions();

        // get count without any filters
        $allEventsCount = $this->Events->find('all', [
            'conditions' => $conditions,
            'contain' => [
                'Workshops'
            ]
        ])->count();
        $this->set('allEventsCount', $allEventsCount);

        $timeRangeDefault = '30days';
        $timeRangeOptions = [
            '30days' => '30 Tage',
            '90days' => '90 Tage',
            'all' => 'alle'
        ];
        $this->set('timeRangeOptions', $timeRangeOptions);

        $selectedCategories = !empty($this->request->getQuery('categories')) ? explode(',', h($this->request->getQuery('categories'))) : [];
        $this->set('selectedCategories', $selectedCategories);

        $this->Category = $this->getTableLocator()->get('Categories');
        $categories = $this->Category->getMainCategoriesForFrontend();

        $preparedCategories = [];
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


            $newUrl = 'categories=' . join(',', $categoryIdsForNewUrl);
            $newUrl = str_replace('categories=,', '&categories=', $newUrl);

            if (empty($this->request->getQuery('keyword'))) {
                $newUrl = '?' . $newUrl;
            } else {
                $newUrl = '?keyword=' . h($this->request->getQuery('keyword')) . '&' . $newUrl;
            }

            if (!empty($this->request->getQuery('timeRange')) && $this->request->getQuery('timeRange') != $timeRangeDefault) {
                $newUrl = $newUrl . '&timeRange=' . h($this->request->getQuery('timeRange'));
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

        $query = $this->Events->find('all', [
            'conditions' => $conditions,
        ]);

        $keyword = '';
        if (!empty($this->request->getQuery('keyword'))) {
            $keyword = h(strtolower(trim($this->request->getQuery('keyword'))));
            $query->where($this->Event->getKeywordSearchConditions($keyword, false));
        }
        $this->set('keyword', $keyword);

        $timeRange = $timeRangeDefault;
        if (!empty($this->request->getQuery('timeRange'))) {
            $timeRange = h(strtolower(trim($this->request->getQuery('timeRange'))));
        }
        if (in_array($timeRange, ['30days', '90days'])) {
            $query->where($this->Event->getTimeRangeCondition($timeRange, false));
        }
        $this->set('timeRange', $timeRange);

        $resetCategoriesUrl = '/reparatur-termine';
        if ($keyword != '') {
            $resetCategoriesUrl = '/reparatur-termine?keyword=' . $keyword;
        }
        if (!empty($this->request->getQuery('timeRange')) && $this->request->getQuery('timeRange') != $timeRangeDefault) {
            $queryStringStartsWith = '?';
            if ($keyword != '') {
                $queryStringStartsWith = '&';
            }
            $resetCategoriesUrl .= $queryStringStartsWith . 'timeRange='.$this->request->getQuery('timeRange');
        }
        $this->set('resetCategoriesUrl', $resetCategoriesUrl);

        if (!empty($this->request->getQuery('categories'))) {
            $categories = explode(',', h($this->request->getQuery('categories')));
            if (!empty($categories)) {
                $query->matching('Categories', function(\Cake\ORM\Query $q) use ($categories) {
                    return $q->where([
                        'Categories.id IN' => $categories
                    ]);
                });
            }
        }
        $query->distinct($this->Events->getListFields());

        $events = $this->paginate($query, [
            'fields' => $this->Event->getListFields(),
            'order' => $this->Event->getListOrder(),
            'contain' => [
                'Workshops',
                'Categories'
            ]
        ]);

        $this->set('events', $events);

        // $events needs to be cloned, because unset($e['workshop']); in combineEventsForMap would also remove it from $events
        // $events cannot be cloned because it is a resultset
        // so call $this->pagniate twice - no performance problem!
        $newEvents = $this->paginate($query, [
            'fields' => $this->Event->getListFields(),
            'order' => $this->Event->getListOrder(),
            'contain' => [
                'Workshops'
            ]
        ]);
        $eventsForMap = $this->combineEventsForMap($newEvents);
        $this->set('eventsForMap', $eventsForMap);

        $urlOptions = [
            'url' => [
                'controller' => 'reparatur-termine',
                'keyword' => $keyword
            ]
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
