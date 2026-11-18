<?php
declare(strict_types=1);
namespace App\Controller;

use App\Controller\Component\StringComponent;
use Cake\Core\Configure;
use Cake\Event\EventInterface;
use Cake\Http\Exception\NotFoundException;
use Eluceo\iCal\Domain\Entity\Calendar;
use App\Model\Entity\Event;
use App\Model\Entity\EventSeries;
use Eluceo\iCal\Domain\Entity\TimeZone;
use \DateTimeZone as PhpDateTimeZone;
use \DateTime as PhpDateTime;
use \DateTimeImmutable as PhpDateTimeImmutable;
use Eluceo\iCal\Domain\ValueObject\DateTime as iCalDateTime;
use Eluceo\iCal\Domain\ValueObject\GeographicPosition;
use Eluceo\iCal\Domain\ValueObject\Location;
use Eluceo\iCal\Domain\ValueObject\TimeSpan;
use Eluceo\iCal\Domain\ValueObject\UniqueIdentifier;
use Eluceo\iCal\Presentation\Factory\CalendarFactory;
use InvalidArgumentException;
use Cake\View\JsonView;
use Cake\I18n\DateTime;
use App\Model\Entity\Workshop;
use App\Model\Table\EventsTable;
use Cake\Datasource\Paging\PaginatedInterface;
use Cake\Http\Response;
use Cake\ORM\Query\SelectQuery;
use Cake\ORM\Exception\PersistenceFailedException;

class EventsController extends AppController
{

    public const string EDIT_SCOPE_EVENT = 'event';
    public const string EDIT_SCOPE_FUTURE_SERIES_EVENTS = 'future_series_events';
    public const string DELETE_SCOPE_EVENT = 'single';
    public const string DELETE_SCOPE_FUTURE_SERIES_EVENTS = 'future_series_events';

    public EventsTable $Event;

    public function beforeFilter(EventInterface $event): void
    {

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

    public function ical(): Response
    {

        if ($this->request->getParam('_ext') != 'ics') {
            throw new NotFoundException();
        }

        $this->disableAutoRender();

        $workshopUid = $this->request->getParam('pass')[0] ?? 0;

        $filename = 'events';
        $conditions = $this->Event->getListConditions();
        if ($workshopUid > 0) {
            $conditions['Workshops.uid'] = $workshopUid;
            $filename = $workshopUid;
        }
        $filename .= '.' . $this->request->getParam('_ext');

        $eventTable = $this->getTableLocator()->get('Events');
        $events = $eventTable->find('all',
            conditions: $conditions,
            contain: [
                'Workshops',
                'Categories',
            ],
            order: $this->Event->getListOrder(),
        );

        $icalEvents = [];

        /** @var Event $event */
        foreach($events as $event) {

            $icalEvent = new \Eluceo\iCal\Domain\Entity\Event(
                new UniqueIdentifier((string) $event->uid),
            );

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
            $startDateTime = PhpDateTimeImmutable::createFromFormat('Y-m-d H:i:s', $start->format('Y-m-d H:i:s'));
            if ($startDateTime === false) {
                continue;
            }
            $start = new iCalDateTime($startDateTime, false);

            $end = new PhpDateTime(
                $event->datumstart->i18nFormat(
                    Configure::read('DateFormat.Database')
                ) . ' ' .
                $event->uhrzeitend->i18nFormat(
                    Configure::read('DateFormat.de.TimeWithSeconds')
                )
            );
            $endDateTime = PhpDateTimeImmutable::createFromFormat('Y-m-d H:i:s', $end->format('Y-m-d H:i:s'));
            if ($endDateTime === false) {
                continue;
            }
            $end = new iCalDateTime($endDateTime, false);
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

    public function myEvents(): void
    {

        $hasEditEventPermissions = $this->isAdmin() || $this->isOrga();

        $this->Workshop = $this->getTableLocator()->get('Workshops');
        if ($this->isAdmin()) {
            $workshops = $this->Workshop->getWorkshopsWithUsers(APP_DELETED);
        } else {
            $workshops = $this->Workshop->getWorkshopsForAssociatedUser($this->loggedUser->uid, APP_DELETED);
        }

        $eventsAssociation = $this->Workshop->getAssociation('Events');
        $eventsAssociation->setConditions([
            'Events.status >=' . APP_OFF,
        ]);

        $workshops->contain([
            'Events' => [
                'sort' => [
                    'Events.datumstart' => 'DESC',
                    'Events.uhrzeitstart' => 'DESC',
                ],
                'InfoSheets' => [
                    'OwnerUsers',
                    'Brands' => function($q) {
                        return $q->select($this->Workshop->Events->InfoSheets->Brands);
                    },
                    'Categories' => function($q) {
                        return $q->select($this->Workshop->Events->InfoSheets->Categories);
                    }
                ],
            ],
        ]);

        $conditions = [
            'InfoSheets.status > ' . APP_DELETED
        ];

        // show only own content for repair helper
        if (!$hasEditEventPermissions) {
            $conditions['InfoSheets.owner'] = $this->isLoggedIn() ? $this->loggedUser->uid : 0;
        }

        $workshops->contain([
            'Events.InfoSheets' => [
                'conditions' => $conditions,
                'sort' => [
                    'InfoSheets.device_name' => 'ASC',
                ],
            ],
        ]);

        $workshops = $this->paginate($workshops, [
            'limit' => 100,
        ]);

        $worknewsTable = $this->getTableLocator()->get('Worknews');
        $seriesUids = [];
        foreach($workshops as $workshop) {
            $workshop->infoSheetCount = 0;
            $workshop->worknewsCount = $worknewsTable->getSubscribers($workshop->uid)->count();
            if (!empty($workshop->events)) {
                foreach($workshop->events as $event) {
                    $workshop->infoSheetCount += count($event->info_sheets);
                    if ($event->series_uid) {
                        $seriesUids[$event->series_uid] = true;
                    }
                }
            }
        }
        /** @var \App\Model\Table\EventSeriesTable $eventSeriesTable */
        $eventSeriesTable = $this->fetchTable('EventSeries');
        $this->set('seriesDescriptions', $eventSeriesTable->getDescriptions(array_keys($seriesUids)));
        $this->set('workshops', $workshops);

        $metaTags = [
            'title' => 'Meine Termine'
        ];
        $this->set('metaTags', $metaTags);

        $this->set('hasEditEventPermissions', $hasEditEventPermissions);
        $this->set('infoSheetColspan', $hasEditEventPermissions ? 11 : 8);

    }

    public function feed(): void
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

    public function delete(int $eventUid): Response
    {
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

        if (!$event instanceof Event) {
            throw new NotFoundException;
        }

        if ($event->series_uid) {
            return $this->deleteSeriesOccurrence($event);
        }

        // keep this line here!!!
        $originalEventStatus = $event->status;

        $patchedEntity = $this->Event->patchEntity(
            $this->Event->get($eventUid),
            ['status' => APP_DELETED]
        );

        if ($this->Event->save($patchedEntity)) {

            /** @var \App\Model\Table\FundingconfirmedeventsTable */
            $fundingconfirmedeventsTable = $this->fetchTable('Fundingconfirmedevents');
            $fundingconfirmedeventsTable->deleteAll([
                'event_uid' => $eventUid,
            ]);

            $this->AppFlash->setFlashMessage('Der Termin wurde erfolgreich gelöscht.');

            if ((int)$originalEventStatus === APP_ON) {
                $this->notifySubscribersAboutDeletedEvent($event, $event->workshop);
            }

        } else {
            $this->AppFlash->setErrorMessage('Beim Löschen ist ein Fehler aufgetreten');
        }

        return $this->redirect($this->getReferer());

    }

    private function deleteSeriesOccurrence(Event $event): Response
    {
        $redirectUrl = $this->getReferer();
        if (!$this->request->is('post')) {
            return $this->redirect($redirectUrl);
        }
        $scope = $this->request->getData('scope');
        if (!in_array($scope, [self::DELETE_SCOPE_EVENT, self::DELETE_SCOPE_FUTURE_SERIES_EVENTS], true)) {
            $this->AppFlash->setFlashError('Bitte wähle, welche Termine gelöscht werden sollen.');
            return $this->redirect($redirectUrl);
        }
        $query = $this->Event->findFutureSeriesEvents($event);
        if ($scope === self::DELETE_SCOPE_EVENT) {
            $query->where(['Events.uid' => $event->uid]);
        }
        /** @var list<Event> $events */
        $events = $query->all()->toList();
        $onlineEvents = array_filter($events, fn(Event $member): bool => (int)$member->status === APP_ON);
        foreach ($events as $member) {
            $member->status = APP_DELETED;
        }
        try {
            $this->Event->getConnection()->transactional(function () use ($events): void {
                foreach ($events as $member) {
                    $this->Event->saveOrFail($member);
                    $this->fetchTable('Fundingconfirmedevents')->deleteAll(['event_uid' => $member->uid]);
                }
            });
        } catch (PersistenceFailedException) {
            $this->AppFlash->setFlashError('Die Termine konnten nicht gelöscht werden. Es wurde kein Termin gelöscht.');
            return $this->redirect($redirectUrl);
        }
        foreach ($onlineEvents as $member) {
            $this->notifySubscribersAboutDeletedEvent($member, $event->workshop);
        }
        $this->AppFlash->setFlashMessage(count($events) === 1 ? 'Der Termin wurde erfolgreich gelöscht.' : count($events) . ' Termine erfolgreich gelöscht.');
        return $this->redirect($redirectUrl);
    }

    public function add(int $preselectedWorkshopUid): ?Response
    {

        $event = $this->Event->newEntity(
            [
                'status' => APP_ON,
                'workshop_uid' => $preselectedWorkshopUid,
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
            return $this->render('edit');
        }
        return null;
    }

    public function duplicate(int $eventUid): Response
    {
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

        if (!$event instanceof Event) {
            throw new NotFoundException;
        }
        $this->setIsCurrentlyUpdated($event->uid);
        $this->set('metaTags', ['title' => 'Termin duplizieren']);
        $this->set('editFormUrl', Configure::read('AppConfig.htmlHelper')->urlEventNew($event->workshop_uid));
        $this->set('preselectedWorkshopUid', $event->workshop_uid);
        $this->set('isDuplicateMode', true);
        $this->_edit([$event], false);
        return $this->render('edit');
    }

    public function edit(int $eventUid): void
    {
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
        if (!$event instanceof Event) {
            throw new NotFoundException;
        }

        $this->setIsCurrentlyUpdated($event->uid);
        $this->set('metaTags', ['title' => 'Termin bearbeiten']);
        $this->set('editFormUrl', Configure::read('AppConfig.htmlHelper')->urlEventEdit($event->uid));
        $this->set('isDuplicateMode', false);

        $seriesEditScope = self::EDIT_SCOPE_EVENT;
        if ($event->series_uid) {
            $series = $this->fetchTable('EventSeries')->get($event->series_uid);
            $this->set('seriesDescription', $series->description);
            $this->set('seriesFutureEventsCount', $this->Event->findFutureSeriesEvents($event)->count());
            $requestedScope = $this->request->is('get')
                ? $this->request->getQuery('scope')
                : $this->request->getData('series.scope');
            if ($requestedScope === self::EDIT_SCOPE_FUTURE_SERIES_EVENTS) {
                $seriesEditScope = self::EDIT_SCOPE_FUTURE_SERIES_EVENTS;
            }
        }
        $this->set('seriesEditScope', $seriesEditScope);

        $worknewsTable = $this->getTableLocator()->get('Worknews');
        $this->set('worknewsCount', $worknewsTable->getSubscribers($event->workshop_uid)->count());

        $eventStartInLessThan7Days = $event->datumstart->isWithinNext('7 days');
        $this->set('eventStartInLessThan7Days', $eventStartInLessThan7Days);
        
        $patchedEntities = $this->_edit([$event], true, $seriesEditScope === self::EDIT_SCOPE_FUTURE_SERIES_EVENTS);
        $patchedEntity = $patchedEntities['events'][0] ?? null;
        if (!$patchedEntity instanceof Event) {
            return;
        }

        // never send notification mail on add
        // if event is edited and renotify is active, send mail to subscriber
        if (!empty($event->workshop)) {
            $workshop = $event->workshop;
        }

        // notify subscribers
        if (isset($workshop) && $patchedEntity->renotify && !empty($patchedEntities['saved'])) {
            $worknewsTable = $this->getTableLocator()->get('Worknews');
            $subscribers = $worknewsTable->getSubscribers($patchedEntity->workshop_uid);
            if ($subscribers->count() === 0) {
                return;
            }
            if ($seriesEditScope === self::EDIT_SCOPE_FUTURE_SERIES_EVENTS) {
                // one mail per changed event within the next 7 days
                foreach ($patchedEntities['events'] as $changedEvent) {
                    $change = $patchedEntities['seriesChanges'][$changedEvent->uid] ?? null;
                    if ($change === null || $change['fields'] === [] || !$changedEvent->datumstart->isWithinNext('7 days')) {
                        continue;
                    }
                    $worknewsTable->sendNotifications($subscribers, 'Veranstaltung geändert: ' . $workshop->name, 'event_changed', $workshop, $changedEvent, $change['fields'], $change['original']);
                }
                return;
            }
            $worknewsTable->sendNotifications($subscribers, 'Veranstaltung geändert: ' . $workshop->name, 'event_changed', $workshop, $patchedEntity, $patchedEntities['dirtyFields'], $patchedEntities['originalValues']);
        }
    }

    /**
     * @param \Cake\ORM\Query\SelectQuery<\App\Model\Entity\Event>|array<int, \App\Model\Entity\Event> $events
     * @return array<string, mixed>
     */
    private function _edit(SelectQuery|array $events, bool $isEditMode, bool $editFollowing = false): array
    {
        if ($events instanceof SelectQuery) {
            $events = $events->toArray();
        }

        $events = array_values($events);

        if ($events === []) {
            return [
                'events' => [],
            ];
        }

        $categoriesTable = $this->getTableLocator()->get('Categories');
        $this->set('categories', $categoriesTable->getForDropdown([APP_ON]));

        $this->set('uid', $events[0]->uid);

        $this->set('seriesSettings', []);

        $this->setReferer();

        if (!empty($this->request->getData())) {
            $patchedEvents = [];
            $editData = [];
            $requestData = $this->request->getData();
            if (!is_array($requestData)) {
                $requestData = [];
            }

            $baseData = $requestData[0] ?? [];
            if (!is_array($baseData)) {
                $baseData = [];
            }
            $seriesData = $requestData['series'] ?? [];
            if (!is_array($seriesData)) {
                $seriesData = [];
            }
            $this->set('seriesSettings', $seriesData);
            $repeat = !empty($seriesData['enabled']);
            if (!$isEditMode && $repeat) {
                return $this->saveSeries($events[0], $baseData, $seriesData);
            }
            foreach($requestData as $data) {
                if (!is_array($data)) {
                    continue; // skip referer
                }
                if (!array_key_exists('datumstart', $data)) {
                    continue; // skip metadata (fields / unlocked / debug)
                }
                $data = array_merge($baseData, $data);
                $data['workshop_uid'] = $events[0]->workshop_uid;
                unset($data['series_uid']);
                if ($isEditMode && $patchedEvents !== []) {
                    continue;
                }

                $datumstart = $data['datumstart'] ?? null;
                if ($datumstart) {
                    $data['datumstart'] = new DateTime($datumstart);
                }
                if ($editFollowing) {
                    // date can only be changed when editing a single event of a series
                    $data['datumstart'] = $events[0]->datumstart;
                }

                $uhrzeitstart = $data['uhrzeitstart'] ?? null;
                if ($uhrzeitstart) {
                    $data['uhrzeitstart'] = new DateTime($uhrzeitstart);
                }

                $uhrzeitend = $data['uhrzeitend'] ?? null;
                if ($uhrzeitend) {
                    $data['uhrzeitend'] = new DateTime($uhrzeitend);
                }

                $useCustomCoordinates = !empty($data['use_custom_coordinates']);
                if (!$useCustomCoordinates) {
                    $addressString = (string)($data['strasse'] ?? '') . ', ' . (string)($data['zip'] ?? '') . ' ' . (string)($data['ort'] ?? '') . ', ' . (string)($data['land'] ?? '');
                    $geoData = $this->geoService->getGeoDataByAddress($addressString);
                    $data['lat'] = $geoData['lat'];
                    $data['lng'] = $geoData['lng'];
                }

                if ($useCustomCoordinates) {
                    $lat = (string)($data['lat'] ?? '');
                    $lng = (string)($data['lng'] ?? '');
                    $geoData = $this->geoService->getGeoDataByCoordinates($lat, $lng);
                    $data['lat'] = str_replace(',', '.', $lat);
                    $data['lng'] = str_replace(',', '.', $lng);
                }
                $data['province_id'] = $geoData['provinceId'] ?? 0;
                if ($isEditMode) {
                    $data['uid'] = $events[0]->uid;
                    $editData = $data;
                }
                $event = clone($events[0]);
                $patchedEvents[] = $this->Event->patchEntity($event, $data);
            }

            $original = $events[0];
            $events = $patchedEvents ?: $events;

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

            if (!$hasErrors && $patchedEvents !== []) {
                $eventsTable = $this->getTableLocator()->get('Events');
                $dirtyFields = $events[0]->getDirty();
                $originalValues = $events[0]->getOriginalValues();
                $seriesChanges = [];
                try {
                    if ($editFollowing) {
                        $events = $this->prepareFollowingEvents($original, $events[0], $editData);
                        foreach ($events as $member) {
                            $seriesChanges[$member->uid] = [
                                'fields' => $member->getDirty(),
                                'original' => $member->getOriginalValues(),
                            ];
                        }
                    }
                    $eventsTable->getConnection()->transactional(function () use ($eventsTable, $events): void {
                        foreach ($events as $e) {
                            $eventsTable->saveOrFail($e);
                        }
                    });
                    foreach ($events as $e) {
                        $this->Event->copyForeignImage($e);
                    }
                } catch (PersistenceFailedException $exception) {
                    if (!$editFollowing) {
                        throw $exception;
                    }
                    $events = [$patchedEvents[0]];
                    $events[0]->setErrors($exception->getEntity()->getErrors());
                    $this->AppFlash->setFlashError('Die Termine konnten nicht gespeichert werden. Es wurde kein Termin gespeichert.');
                    $this->set('events', $events);
                    $this->set('isEditMode', $isEditMode);
                    $render = $this->render('edit');
                    return ['events' => $events, 'saved' => false];
                }

                $message = 'Termine';
                if (count($events) == 1) {
                    $message = 'Termin';
                }
                $message = count($events) . ' ' . $message . ' erfolgreich gespeichert.';
                $this->AppFlash->setFlashMessage($message);
                $redirect = $this->redirect($this->getPreparedReferer());
                return [
                    'events' => $events,
                    'dirtyFields' => $dirtyFields,
                    'originalValues' => $originalValues,
                    'seriesChanges' => $seriesChanges,
                    'saved' => true,
                ];
            }

        }

        $this->set('events', $events);
        $this->set('isEditMode', $isEditMode);
        $render = $this->render('edit');
        return [
            'events' => $events,
        ];


    }

    /**
     * @param array<string, mixed> $data
     * @return list<Event>
     */
    private function prepareFollowingEvents(Event $original, Event $edited, array $data): array
    {
        $changes = array_intersect_key($data, array_flip($edited->getDirty()));
        unset($changes['uid'], $changes['workshop_uid'], $changes['renotify']);
        if ($changes === []) {
            return [$edited];
        }

        $validationFields = ['uid', 'workshop_uid', 'datumstart', 'uhrzeitstart', 'uhrzeitend', 'ort', 'zip', 'strasse', 'status'];
        $events = [$edited];
        $members = $this->Event->findFutureSeriesEvents($original)->where(['Events.uid !=' => $original->uid]);
        foreach ($members as $member) {
            $memberData = $changes + $member->extract($validationFields);
            $this->Event->patchEntity($member, $memberData);
            $events[] = $member;
        }

        return $events;
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $settings
     * @return array<string, mixed>
     */
    private function saveSeries(Event $original, array $data, array $settings): array
    {
        $eventSeriesTable = $this->fetchTable('EventSeries');
        $events = [$original];
        $error = null;
        try {
            if (empty($data['datumstart'])) {
                throw new InvalidArgumentException('Bitte trage ein Datum ein.');
            }
            $settings['start'] = (new DateTime($data['datumstart']))->format('Y-m-d');
            $seriesEnd = (string)($settings['end'] ?? '');
            $seriesEndDate = \DateTimeImmutable::createFromFormat('!d.m.Y', $seriesEnd);
            if ($seriesEndDate !== false && $seriesEndDate->format('d.m.Y') === $seriesEnd) {
                $settings['end'] = $seriesEndDate->format('Y-m-d');
            }
            $this->set('seriesSettings', $settings);
            if (!empty($settings['preview'])) {
                $series = EventSeries::fromSettings($settings);
                $this->set('seriesPreview', $series->dates);
                $events = $this->seriesFormEvents($original, $data);
            } else {
                $data = $this->normalizeSeriesData($data);
                $series = $eventSeriesTable->prepare($original, $data, $settings);
                $standaloneEvents = [];
                foreach ($this->request->getData() as $index => $extra) {
                    if (!is_numeric($index) || (int)$index === 0 || !is_array($extra) || !array_key_exists('datumstart', $extra)) {
                        continue;
                    }
                    $extra = $this->normalizeSeriesData(array_replace($data, $extra));
                    unset($extra['uid'], $extra['series_uid']);
                    $extra['workshop_uid'] = $original->workshop_uid;
                    $standaloneEvents[] = $this->Event->patchEntity(clone $original, $extra);
                }
                $members = [...$series->events, ...$standaloneEvents];
                $keys = [];
                foreach ($members as $member) {
                    if ((int)$member->status === APP_DELETED || $member->hasErrors()) {
                        continue;
                    }
                    $key = implode('|', [(string)$member->datumstart, (string)$member->uhrzeitstart, (string)$member->uhrzeitend, $member->ort, $member->zip, $member->strasse]);
                    if (isset($keys[$key])) {
                        $member->setError('datumstart', 'Dieser Termin ist bereits in der Terminserie enthalten.');
                    }
                    $keys[$key] = true;
                }
                $invalidEvents = array_filter($members, static fn(Event $event): bool => $event->hasErrors());
                if ($invalidEvents !== []) {
                    $events = $this->seriesFormEvents($original, $data);
                    $events[0]->setErrors(reset($invalidEvents)->getErrors());
                    $error = 'Bitte korrigiere die markierten Felder. Es wurde kein Termin gespeichert.';
                } else {
                    $this->Event->getConnection()->transactional(function () use ($series, $eventSeriesTable, $standaloneEvents): void {
                        $eventSeriesTable->saveOrFail($series, ['associated' => ['Events.Categories']]);
                        foreach ($standaloneEvents as $standaloneEvent) {
                            $this->Event->saveOrFail($standaloneEvent);
                        }
                    });
                    // reload saved events: uids of associated series events are not set on the patched entities
                    $savedEvents = $this->Event->find()->where([
                        'Events.status >' => APP_DELETED,
                        'OR' => [
                            'Events.series_uid' => $series->uid,
                            'Events.uid IN' => array_map(static fn(Event $event): int => (int)$event->uid, $standaloneEvents) ?: [0],
                        ],
                    ])->all();
                    foreach ($savedEvents as $savedEvent) {
                        $this->Event->copyForeignImage($savedEvent);
                    }
                    $count = $savedEvents->count();
                    $this->AppFlash->setFlashMessage($count . ' Termine erfolgreich gespeichert.');
                    $redirect = $this->redirect($this->getPreparedReferer());
                    return ['events' => $members, 'saved' => true];
                }
            }
        } catch (InvalidArgumentException|PersistenceFailedException $exception) {
            $error = $exception instanceof InvalidArgumentException
                ? $exception->getMessage()
                : 'Die Termine konnten nicht gespeichert werden. Es wurde kein Termin gespeichert.';
            $events = $this->seriesFormEvents($original, $data);
            if (empty($data['datumstart']) && $error === 'Bitte trage ein Datum ein.') {
                $events[0]->setError('datumstart', ['required' => $error]);
                $error = null;
            }
        }
        if ($error !== null) {
            $this->AppFlash->setFlashError($error);
            $this->set('seriesError', $error);
        }
        $this->set('events', $events);
        $this->set('isEditMode', false);
        $render = $this->render('edit');
        return ['events' => $events, 'saved' => false];
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function normalizeSeriesData(array $data): array
    {
        foreach (['datumstart', 'uhrzeitstart', 'uhrzeitend'] as $field) {
            if (!empty($data[$field])) {
                $data[$field] = new DateTime($data[$field]);
            }
        }
        if (empty($data['use_custom_coordinates'])) {
            $address = (string)($data['strasse'] ?? '') . ', ' . (string)($data['zip'] ?? '') . ' ' . (string)($data['ort'] ?? '') . ', ' . (string)($data['land'] ?? '');
            $geo = $this->geoService->getGeoDataByAddress($address);
            $data['lat'] = $geo['lat'];
            $data['lng'] = $geo['lng'];
        } else {
            $data['lat'] = str_replace(',', '.', (string)($data['lat'] ?? ''));
            $data['lng'] = str_replace(',', '.', (string)($data['lng'] ?? ''));
            $geo = $this->geoService->getGeoDataByCoordinates($data['lat'], $data['lng']);
        }
        $data['province_id'] = $geo['provinceId'] ?? 0;
        return $data;
    }

    /**
     * @param array<string, mixed> $data
     * @return list<Event>
     */
    private function seriesFormEvents(Event $original, array $data): array
    {
        $events = [$this->Event->patchEntity(clone $original, $data, ['validate' => false])];
        foreach ($this->request->getData() as $index => $extra) {
            if (is_numeric($index) && (int)$index > 0 && is_array($extra) && array_key_exists('datumstart', $extra)) {
                $events[] = $this->Event->patchEntity(clone $original, array_replace($data, $extra), ['validate' => false]);
            }
        }
        return $events;
    }

    private function notifySubscribersAboutDeletedEvent(Event $event, Workshop $workshop): void
    {
        if (!$event->datumstart->isWithinNext('7 days')) {
            return;
        }
        /** @var \App\Model\Table\WorknewsTable $worknewsTable */
        $worknewsTable = $this->fetchTable('Worknews');
        $worknewsTable->sendNotifications($worknewsTable->getSubscribers($event->workshop_uid), 'Veranstaltung gelöscht', 'event_deleted', $workshop, $event);
    }

    public function ajaxGetSeriesInfo(int $eventUid): void
    {
        $this->request = $this->request->withParam('_ext', 'json');
        $event = $this->Event->get($eventUid);
        $this->set('futureSeriesEventsCount', $event->series_uid ? $this->Event->findFutureSeriesEvents($event)->count() : 0);
        $this->viewBuilder()->setOption('serialize', ['futureSeriesEventsCount']);
    }

    public function ajaxGetAllEventsForMap(): void
    {

        if (Configure::read('debug') && $this->isAdmin()) {
            ini_set('memory_limit', '1024M');
        }

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
                'EventCategories',
            ],
        );
        $events->distinct($this->Event->getListFields());

        if (!empty($this->request->getQuery('keyword'))) {
            $keyword = StringComponent::stripUtf8mb4Chars(h(strtolower(trim((string) $this->request->getQuery('keyword')))));
            if ($keyword !== '' && $keyword !== 'null') {
                $events->where($this->Event->getKeywordSearchConditions($keyword, false));
            }
        }

        if (! $allParamsEmpty) {
            $events->where($this->Event->getKeywordSearchConditions($keyword, true));
        }

        if (!empty($this->request->getQuery('categories'))) {
            $categoriesRaw = (string)$this->request->getQuery('categories');
            $categories = array_values(array_filter(explode(',', h($categoriesRaw)), static fn($v) => $v !== ''));
            if (count($categories) > 0) {
                $events->innerJoinWith('EventCategories', function ($q) use ($categories) {
                    return $q->where([
                        'EventCategories.category_id NOT IN' => $categories,
                    ]);
                });
            }
        }

        $categoriesTable = $this->getTableLocator()->get('Categories');
        $categoriesMap = $categoriesTable->getMainCategoriesForFrontendIndexedById();
        $this->set('categoriesMap', $categoriesMap);

        foreach($events as $event) {
            $event->categories = [];
            foreach($event->event_categories as $category) {
                if (!isset($categoriesMap[$category->category_id])) {
                    continue;
                }
                $event->categories[] = $categoriesMap[$category->category_id];
            }
            unset($event->event_categories);
        }

        $this->set([
            'status' => 1,
            'message' => 'ok',
            'events' => $this->combineEventsForMap($events->toArray()),
        ]);
        $this->viewBuilder()->setOption('serialize', ['status', 'message', 'events']);
    }

    public function all(): void
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
        $eventsTable = $this->getTableLocator()->get('Events');
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

        $categoriesTable = $this->getTableLocator()->get('Categories');
        $categoriesMap = $categoriesTable->getMainCategoriesForFrontendIndexedById();
        $this->set('categoriesMap', $categoriesMap);

        $preparedCategories = [];
        $categoryClass = '';
        foreach ($categoriesMap as $category) {

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

            if (!empty($this->request->getQuery('provinceId'))) {
                $newUrl = $newUrl . '&provinceId=' . h($this->request->getQuery('provinceId'));
            }
            if (!empty($this->request->getQuery('isOnlineEvent'))) {
                $newUrl = $newUrl . '&isOnlineEvent=' . h($this->request->getQuery('isOnlineEvent'));
            }

            $newUrl = str_replace('//', '/', $newUrl);

            $category->href = $newUrl;
            $category->class = $categoryClass;
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
            $keyword = StringComponent::stripUtf8mb4Chars(h(strtolower(trim((string) $this->request->getQuery('keyword')))));
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
            $categoriesRaw = (string)$this->request->getQuery('categories');
            $categories = array_values(array_filter(explode(',', h($categoriesRaw)), static fn($v) => $v !== ''));
            if (count($categories) > 0) {
                $query->innerJoinWith('EventCategories', function ($q) use ($categories) {
                    return $q->where([
                        'EventCategories.category_id IN' => $categories,
                    ]);
                });
            }
        }

        if ($isOnlineEvent) {
            $query->where(['Events.is_online_event' => 1]);
        }

        $query->select($eventsTable->getListFields());
        $query->orderBy($eventsTable->getListOrder());
        $query->contain([
            'Workshops',
            'EventCategories',
        ]);

        $fallbackNearbyQuery = clone $query;
        if ($keyword != '') {
            $query->where($this->Event->getKeywordSearchConditions($keyword, false));
        }

        $citiesTable = $this->getTableLocator()->get('Cities');
        $nearbyQueryFallbackResult = $citiesTable->getFallbackNearbyQuery($query, $fallbackNearbyQuery, $keyword, 'Events');
        $this->set('fallbackNearbyUsed', $nearbyQueryFallbackResult['is_fallback']);

        $query = $nearbyQueryFallbackResult['query'];
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
    * @param \Cake\ORM\Query\SelectQuery<array<int|string, mixed>|\Cake\Datasource\EntityInterface>|\Cake\Datasource\Paging\PaginatedInterface<array-key, \App\Model\Entity\Event>|array<int, mixed> $events
     * @return array<int, mixed>
     */
    private function combineEventsForMap(PaginatedInterface|SelectQuery|array $events): array
    {
        $eventsForMap1 = [];

        foreach ($events as $event) {
            if (!$event instanceof Event) {
                continue;
            }
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
                unset($e->workshop);
                $preparedEvent['Events'][] = $e;
            }
            $eventsForMap[] = $preparedEvent;
        }
        return $eventsForMap;
    }

}
?>
