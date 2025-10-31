<?php
declare(strict_types=1);
namespace App\Controller;

use App\Controller\Component\StringComponent;
use Cake\Core\Configure;
use Cake\Http\Exception\NotFoundException;
use League\Csv\Writer;
use Cake\Http\Response;
use App\Model\Entity\InfoSheet;

class InfoSheetsController extends AppController
{

    public function fullDownload(string $date=''): Response
    {

        $query = file_get_contents(ROOT . DS . 'config' . DS. 'sql' . DS . 'info-sheets-full-download.sql');
        $params = [];
        $filename = 'repair-data-export';
        if ($date != '') {
            $query = preg_replace('/WHERE 1/', 'WHERE 1 AND e.datumstart <= :date', $query);
            $params['date'] = $date;
            $filename .= '-until-' . $date;
        }

        /* @var InfoSheetsTable  $infoSheetsTable */
        $infoSheetsTable = $this->getTableLocator()->get('InfoSheets');
        $statement = $infoSheetsTable->getConnection()->execute($query, $params);
        $records = $statement->fetchAll('assoc');
        if (empty($records)) {
            throw new NotFoundException('no repair data found');
        }

        $writer = Writer::fromString();
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

    public function download(int $workshopUid, string $year=''): Response
    {

        /* @var WorkshopsTable $workshopsTable */
        $workshopsTable = $this->getTableLocator()->get('Workshops');
        $workshop = $workshopsTable->find('all', conditions: [
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
        if (in_array($year, Configure::read('AppConfig.timeHelper')->getAllYearsUntilThisYear((int) date('Y'), 2010))) {
            $query = preg_replace('/WHERE 1/', 'WHERE 1 AND DATE_FORMAT(e.datumstart, \'%Y\') = :year', $query);
            $params['year'] = $year;
            $filename .= '-' . $year;
        }

        /* @var App\Model\Table\InfoSheetsTable  $infoSheetsTable */
        $infoSheetsTable = $this->getTableLocator()->get('InfoSheets');
        $statement = $infoSheetsTable->getConnection()->execute($query, $params);
        $records = $statement->fetchAll('assoc');
        if (empty($records)) {
            throw new NotFoundException('info sheets not found');
        }

        $writer = Writer::fromString();
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

    public function delete(int $infoSheetUid): Response
    {
        /* @var App\Model\Table\InfoSheetsTable  $infoSheetsTable */
        $infoSheetsTable = $this->getTableLocator()->get('InfoSheets');
        $infoSheet = $infoSheetsTable->find('all', conditions: [
            'InfoSheets.uid' => $infoSheetUid,
            'InfoSheets.status >= ' . APP_DELETED
        ])->first();

        if (empty($infoSheet)) {
            throw new NotFoundException;
        }

        $patchedEntity = $infoSheetsTable->patchEntity(
            $infoSheetsTable->get($infoSheetUid),
            ['status' => APP_DELETED]
        );

        if ($infoSheetsTable->save($patchedEntity)) {
            $this->AppFlash->setFlashMessage('Der Laufzettel wurde erfolgreich gelöscht.');
        } else {
            $this->AppFlash->setErrorMessage('Beim Löschen ist ein Fehler aufgetreten');
        }

        return $this->redirect($this->getReferer());

    }

    public function add(int $eventUid): void
    {
        $eventsTable = $this->getTableLocator()->get('Events');
        $eventsTable->getAssociation('Workshops')->setConditions(['Workshops.status > ' . APP_DELETED]);
        $event = $eventsTable->find('all',
        conditions: [
            'Events.uid' => $eventUid
        ],
        contain: [
            'Workshops'
        ]);

        $infoSheetsTable = $this->getTableLocator()->get('InfoSheets');
        $infoSheet = $infoSheetsTable->newEntity(
            [
                'status' => APP_ON,
                'event_uid' => $eventUid
            ],
            ['validate' => false]
        );
        $infoSheet->event = $event->first();

        $this->set('metaTags', ['title' => 'Laufzettel erstellen']);

        $this->set('eventUid', $eventUid);

        $this->_edit($infoSheet, false);

        // assures rendering of success message on redirected page and NOT before and then not showing it
        if (empty($this->request->getData())) {
            $this->render('edit');
        }
    }

    public function edit(int $infoSheetUid): void
    {
        $infoSheetsTable = $this->getTableLocator()->get('InfoSheets');
        $infoSheet = $infoSheetsTable->find('all',
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

        $eventsTable = $this->getTableLocator()->get('Events');
        $events = $eventsTable->find('all',
            conditions: [
                'Events.workshop_uid' => $infoSheet->event->workshop_uid,
                'Events.status >' . APP_DELETED,
            ],
            order: $eventsTable->getListOrder(),
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

    private function _edit(InfoSheet $infoSheet, bool $isEditMode): ?Response
    {

        $this->set('uid', $infoSheet->uid);
        $categoriesTable = $this->getTableLocator()->get('Categories');
        $categoriesForSubcategory = $categoriesTable->getForSubcategoryDropdown();
        $categoriesForSubcategory['Kategorie nicht vorhanden?'] = [
            '-1' => 'Unterkategorie hinzufügen'
        ];
        $this->set('categoriesForSubcategory', $categoriesForSubcategory);
        $this->set('categories', $categoriesTable->getForDropdown([APP_ON]));

        $brandsTable = $this->getTableLocator()->get('Brands');
        $brandsForDropdown = $brandsTable->getForDropdown();
        $brandsForDropdown['Marke nicht vorhanden?'] = [
            '-1' => 'Marke hinzufügen'
        ];
        $this->set('brands', $brandsForDropdown);

        $formFieldsTable = $this->getTableLocator()->get('FormFields');
        $powerSupplyFormField = $formFieldsTable->getForForm(1);
        $this->set('powerSupplyFormField', $powerSupplyFormField);

        $defectFoundReasonFormField = $formFieldsTable->getForForm(3);
        $this->set('defectFoundReasonFormField', $defectFoundReasonFormField);

        $repairPostponedReasonFormField = $formFieldsTable->getForForm(4);
        $this->set('repairPostponedReasonFormField', $repairPostponedReasonFormField);

        $noRepairReasonFormField = $formFieldsTable->getForForm(5);
        $this->set('noRepairReasonFormField', $noRepairReasonFormField);

        $deviceMustNotBeUsedAnymoreFormField = $formFieldsTable->getForForm(6);
        $this->set('deviceMustNotBeUsedAnymoreFormField', $deviceMustNotBeUsedAnymoreFormField);

        $this->setReferer();

        if (!empty($this->request->getData())) {

            $infoSheetsTable = $this->getTableLocator()->get('InfoSheets');
            $patchedEntity = $infoSheetsTable->getPatchedEntityForAdminEdit($infoSheet, $this->request->getData());

            $errors = $patchedEntity->getErrors();
            if (empty($errors)) {

                $patchedEntity = $this->patchEntityWithCurrentlyUpdatedFields($patchedEntity);
                $entity = $this->stripTagsFromFields($patchedEntity, 'InfoSheet');

                if ($patchedEntity->category_id == -1) {
                    $category = $categoriesTable->save(
                        $categoriesTable->newEntity(
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
                    $brand = $brandsTable->save(
                        $brandsTable->newEntity(
                            [
                                'name' => $patchedEntity->new_brand_name,
                                'status' => $this->isAdmin() ? APP_ON : APP_OFF,
                                'owner' => $this->isLoggedIn() ? $this->loggedUser->uid : 0
                            ]
                        ));
                    $entity->brand_id = $brand->id;
                }

                if ($infoSheetsTable->save($entity)) {
                    $this->AppFlash->setFlashMessage($infoSheetsTable->name_de . ' erfolgreich gespeichert.');
                    if (in_array('save-button', array_keys($this->request->getData()))) {
                        $redirectUrl = Configure::read('AppConfig.htmlHelper')->urlMyEvents();
                        $redirectUrl = $this->addRefererParamsToUrl($redirectUrl);
                        return $this->redirect($redirectUrl);
                    } else {
                        return $this->redirect($this->getPreparedReferer());
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

        return null;

    }

}
?>