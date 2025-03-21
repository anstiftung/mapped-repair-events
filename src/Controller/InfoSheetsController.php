<?php
declare(strict_types=1);
namespace App\Controller;

use App\Controller\Component\StringComponent;
use App\Model\Table\BrandsTable;
use App\Model\Table\CategoriesTable;
use App\Model\Table\EventsTable;
use App\Model\Table\FormFieldsTable;
use App\Model\Table\InfoSheetsTable;
use App\Model\Table\WorkshopsTable;
use Cake\Core\Configure;
use Cake\Event\EventInterface;
use Cake\Http\Exception\NotFoundException;
use League\Csv\Writer;
use Cake\Http\Response;

class InfoSheetsController extends AppController
{

    public InfoSheetsTable $InfoSheet;
    public EventsTable $Event;
    public WorkshopsTable $Workshop;
    public CategoriesTable $Category;
    public BrandsTable $Brand;
    public FormFieldsTable $FormField;
    
    public function beforeFilter(EventInterface $event): void
    {

        parent::beforeFilter($event);
        $this->InfoSheet = $this->getTableLocator()->get('InfoSheets');
    }

    public function fullDownload($date=null): Response
    {

        $query = file_get_contents(ROOT . DS . 'config' . DS. 'sql' . DS . 'info-sheets-full-download.sql');
        $params = [];
        $filename = 'repair-data-export';
        if (!is_null($date)) {
            $query = preg_replace('/WHERE 1/', 'WHERE 1 AND e.datumstart <= :date', $query);
            $params['date'] = $date;
            $filename .= '-until-' . $date;
        }

        $statement = $this->InfoSheet->getConnection()->execute($query, $params);
        $records = $statement->fetchAll('assoc');
        if (empty($records)) {
            throw new NotFoundException('no repair data found');
        }

        $writer = Writer::createFromString();
        $writer->insertOne(array_keys($records[0]));
        $id = 1;
        foreach($records as &$record) {
            $record['id'] = 'anstiftung_' . $id;
            $record['problem'] = str_replace("\r\n", " ", $record['problem']);
            $id++;
        }
        $writer->insertAll($records);

        $this->disableAutoRender();

        $response = $this->response;
        $response = $response->withStringBody($writer->toString());
        $response = $response->withCharset('UTF-8');
        $response = $response->withDownload($filename . '.csv');

        return $response;

    }

    public function download($workshopUid, $year=null): Response
    {

        $this->Workshop = $this->getTableLocator()->get('Workshops');
        $workshop = $this->Workshop->find('all', conditions: [
            'Workshops.uid' => $workshopUid
        ])->first();

        if (empty($workshop)) {
            throw new NotFoundException('workshop not found');
        }

        $query = file_get_contents(ROOT . DS . 'config' . DS. 'sql' . DS . 'info-sheets-download.sql');
        $params = [
            'workshopUid' => $workshopUid
        ];
        $filename = 'Laufzettel-Download-' . StringComponent::slugifyAndKeepCase($workshop->name);
        if (in_array($year, Configure::read('AppConfig.timeHelper')->getAllYearsUntilThisYear(date('Y'), 2010))) {
            $query = preg_replace('/WHERE 1/', 'WHERE 1 AND DATE_FORMAT(e.datumstart, \'%Y\') = :year', $query);
            $params['year'] = $year;
            $filename .= '-' . $year;
        }

        $statement = $this->InfoSheet->getConnection()->execute($query, $params);
        $records = $statement->fetchAll('assoc');
        if (empty($records)) {
            throw new NotFoundException('info sheets not found');
        }

        $writer = Writer::createFromString();
        foreach($records as &$record) {
            if ($record['Fehlerbeschreibung'] != '') {
                $record['Fehlerbeschreibung'] = str_replace("\r\n", " ", $record['Fehlerbeschreibung']);
            }
        }
        $writer->insertOne(array_keys($records[0]));
        $writer->insertAll($records);

        $this->disableAutoRender();

        $response = $this->response;
        $response = $response->withStringBody($writer->toString());
        $response = $response->withCharset('UTF-8');
        $response = $response->withDownload($filename . '.csv');

        return $response;
    }

    public function delete($infoSheetUid): void
    {
        if ($infoSheetUid === null) {
            throw new NotFoundException;
        }

        $infoSheet = $this->InfoSheet->find('all', conditions: [
            'InfoSheets.uid' => $infoSheetUid,
            'InfoSheets.status >= ' . APP_DELETED
        ])->first();

        if (empty($infoSheet)) {
            throw new NotFoundException;
        }

        $patchedEntity = $this->InfoSheet->patchEntity(
            $this->InfoSheet->get($infoSheetUid),
            ['status' => APP_DELETED]
        );

        if ($this->InfoSheet->save($patchedEntity)) {
            $this->AppFlash->setFlashMessage('Der Laufzettel wurde erfolgreich gelöscht.');
        } else {
            $this->AppFlash->setErrorMessage('Beim Löschen ist ein Fehler aufgetreten');
        }

        $this->redirect($this->getReferer());

    }

    public function add($eventUid): void
    {

        if ($eventUid === null) {
            throw new NotFoundException;
        }

        $this->Event = $this->getTableLocator()->get('Events');

        $this->Event->getAssociation('Workshops')->setConditions(['Workshops.status > ' . APP_DELETED]);
        $event = $this->Event->find('all',
        conditions: [
            'Events.uid' => $eventUid
        ],
        contain: [
            'Workshops'
        ]);

        $infoSheet = $this->InfoSheet->newEntity(
            [
                'status' => APP_ON,
                'event_uid' => $eventUid
            ],
            ['validate' => false]
        );
        $infoSheet->event = $event->first();

        $this->set('metaTags', ['title' => 'Laufzettel erstellen']);

        $this->set('eventUid', $eventUid);
        $this->set('editFormUrl', Configure::read('AppConfig.htmlHelper')->urlInfoSheetNew($eventUid));

        $this->_edit($infoSheet, false);

        // assures rendering of success message on redirected page and NOT before and then not showing it
        if (empty($this->request->getData())) {
            $this->render('edit');
        }
    }

    public function edit(int $infoSheetUid): void
    {
        $infoSheet = $this->InfoSheet->find('all',
        conditions: [
            'InfoSheets.uid' => $infoSheetUid,
            'InfoSheets.status >= ' . APP_DELETED
        ],
        contain: [
            'Events.Workshops',
            'Categories',
            'FormFieldOptions'
        ])->first();

        if (empty($infoSheet)) {
            throw new NotFoundException;
        }

        $this->setIsCurrentlyUpdated($infoSheet->uid);
        $this->set('metaTags', ['title' => 'Laufzettel bearbeiten']);
        $this->set('editFormUrl', Configure::read('AppConfig.htmlHelper')->urlInfoSheetEdit($infoSheet->uid));

        $events = $this->InfoSheet->Events->find('all',
            conditions: [
                'Events.workshop_uid' => $infoSheet->event->workshop_uid,
                'Events.status >' . APP_DELETED,
            ],
            order: $this->InfoSheet->Events->getListOrder(),
        );
        $eventsForDropdown = [];
        foreach($events as $event) {
            $label = $event->datumstart->i18nFormat(Configure::read('DateFormat.de.DateLong2'));
            if ($event->uhrzeitstart_formatted != '00:00' && $event->uhrzeitend_formatted != '00:00') {
                $label .= ', ' . $event->uhrzeitstart_formatted . ' - ' . $event->uhrzeitend_formatted . ' Uhr';
            }
            $label .= ', Termin-ID: ' . $event->uid;
            $eventsForDropdown[$event->uid] = $label;
        }
        $this->set('eventsForDropdown', $eventsForDropdown);
        $this->_edit($infoSheet, true);
    }

    private function _edit($infoSheet, $isEditMode): void
    {

        $this->set('uid', $infoSheet->uid);

        $this->Category = $this->getTableLocator()->get('Categories');
        $categoriesForSubcategory = $this->Category->getForSubcategoryDropdown();
        $categoriesForSubcategory['Kategorie nicht vorhanden?'] = [
            '-1' => 'Unterkategorie hinzufügen'
        ];
        $this->set('categoriesForSubcategory', $categoriesForSubcategory);
        $this->set('categories', $this->Category->getForDropdown(APP_ON));

        $this->Brand = $this->getTableLocator()->get('Brands');
        $brandsForDropdown = $this->Brand->getForDropdown();
        $brandsForDropdown['Marke nicht vorhanden?'] = [
            '-1' => 'Marke hinzufügen'
        ];
        $this->set('brands', $brandsForDropdown);

        $this->FormField = $this->getTableLocator()->get('FormFields');

        $powerSupplyFormField = $this->FormField->getForForm(1);
        $this->set('powerSupplyFormField', $powerSupplyFormField);

        $defectFoundReasonFormField = $this->FormField->getForForm(3);
        $this->set('defectFoundReasonFormField', $defectFoundReasonFormField);

        $repairPostponedReasonFormField = $this->FormField->getForForm(4);
        $this->set('repairPostponedReasonFormField', $repairPostponedReasonFormField);

        $noRepairReasonFormField = $this->FormField->getForForm(5);
        $this->set('noRepairReasonFormField', $noRepairReasonFormField);

        $deviceMustNotBeUsedAnymoreFormField = $this->FormField->getForForm(6);
        $this->set('deviceMustNotBeUsedAnymoreFormField', $deviceMustNotBeUsedAnymoreFormField);

        $this->setReferer();

        if (!empty($this->request->getData())) {

            $patchedEntity = $this->InfoSheet->getPatchedEntityForAdminEdit($infoSheet, $this->request->getData());

            $errors = $patchedEntity->getErrors();
            if (empty($errors)) {

                $patchedEntity = $this->patchEntityWithCurrentlyUpdatedFields($patchedEntity);
                $entity = $this->stripTagsFromFields($patchedEntity, 'InfoSheet');

                if ($patchedEntity->category_id == -1) {
                    $category = $this->Category->save(
                        $this->Category->newEntity(
                            [
                            'name' => $patchedEntity->new_subcategory_name,
                            'parent_id' => $patchedEntity->new_subcategory_parent_id,
                            'icon' => StringComponent::slugify($patchedEntity->new_subcategory_name),
                            'status' => $this->isAdmin() ? APP_ON : APP_OFF,
                            'owner' => $this->isLoggedIn() ? $this->loggedUser->uid : 0
                        ]
                    ));
                    $entity->category_id = $category->id;
                }

                if ($patchedEntity->brand_id == -1) {
                    $brand = $this->Brand->save(
                        $this->Brand->newEntity(
                            [
                                'name' => $patchedEntity->new_brand_name,
                                'status' => $this->isAdmin() ? APP_ON : APP_OFF,
                                'owner' => $this->isLoggedIn() ? $this->loggedUser->uid : 0
                            ]
                        ));
                    $entity->brand_id = $brand->id;
                }

                if ($this->InfoSheet->save($entity)) {
                    $this->AppFlash->setFlashMessage($this->InfoSheet->name_de . ' erfolgreich gespeichert.');
                    if (in_array('save-button', array_keys($this->request->getData()))) {
                        $this->redirect(Configure::read('AppConfig.htmlHelper')->urlMyEvents());
                    } else {
                        $this->redirect($this->getPreparedReferer());
                    }
                } else {
                    $this->AppFlash->setFlashError($this->InfoSheet->name_de . ' <b>nicht</b>erfolgreich gespeichert.');
                }

            } else {
                $infoSheet = $patchedEntity;
            }
        }

        $this->set('infoSheet', $infoSheet);
        $this->set('isEditMode', $isEditMode);

        if (!empty($errors)) {
            $this->render('edit');
        }

    }

}
?>