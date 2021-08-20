<?php
namespace App\Controller;

use App\Controller\Component\StringComponent;
use Cake\Core\Configure;
use Cake\Event\EventInterface;
use Cake\Http\Exception\NotFoundException;
use League\Csv\Writer;

class InfoSheetsController extends AppController
{

    public function beforeFilter(EventInterface $event) {

        parent::beforeFilter($event);
        $this->InfoSheet = $this->getTableLocator()->get('InfoSheets');
    }

    public function isAuthorized($user)
    {

        if (in_array($this->request->getParam('action'), ['fullDownload'])) {
            if ($this->AppAuth->isAdmin()) {
                return true;
            }
        }

        if (in_array($this->request->getParam('action'), ['download'])) {

            if ($this->AppAuth->isAdmin()) {
                return true;
            }

            if ($this->AppAuth->isOrga()) {
                $workshopUid = (int) $this->request->getParam('pass')[0];
                $this->Workshop = $this->getTableLocator()->get('Workshops');
                $workshop = $this->Workshop->getWorkshopForIsUserInOrgaTeamCheck($workshopUid);
                if ($this->Workshop->isUserInOrgaTeam($this->AppAuth->user(), $workshop)) {
                    return true;
                }
            }

        }

        if (in_array($this->request->getParam('action'), ['edit', 'delete'])) {

            // admin are allowd to edit and delete all info sheets
            if ($this->AppAuth->isAdmin()) {
                return true;
            }

            $infoSheetUid = (int) $this->request->getParam('pass')[0];
            $this->InfoSheet = $this->getTableLocator()->get('InfoSheets');

            // orgas are allowed to edit and delete only info sheets of associated workshops
            if ($this->AppAuth->isOrga()) {

                $infoSheet = $this->InfoSheet->find('all', [
                    'conditions' => [
                        'InfoSheets.uid' => $infoSheetUid,
                        'InfoSheets.status > ' . APP_DELETED
                    ],
                    'contain' => [
                        'Events'
                    ]
                ])->first();

                $workshopUid = $infoSheet->event->workshop_uid;
                $this->Workshop = $this->getTableLocator()->get('Workshops');
                $workshop = $this->Workshop->getWorkshopForIsUserInOrgaTeamCheck($workshopUid);
                if ($this->Workshop->isUserInOrgaTeam($this->AppAuth->user(), $workshop)) {
                    return true;
                }

            }

            // repairhelpers are allowed to edit and delete only own info sheets
            if ($this->AppAuth->isRepairhelper()) {
                $infoSheet = $this->InfoSheet->find('all', [
                    'conditions' => [
                        'InfoSheets.uid' => $infoSheetUid,
                        'InfoSheets.owner' => $this->AppAuth->getUserUid(),
                        'InfoSheets.status > ' . APP_DELETED
                    ]
                ])->first();
                if (!empty($infoSheet)) {
                    return true;
                }
            }

            return false;
        }

        return $this->AppAuth->user();

    }

    public function fullDownload($date=null)
    {

        $query = file_get_contents(ROOT . DS . 'config' . DS. 'sql' . DS . 'repair-data-full-export.sql');
        $params = [];
        $filename = 'repair-data-export';
        if (!is_null($date)) {
            $query = preg_replace('/WHERE 1/', 'WHERE 1 AND e.datumstart <= :date', $query);
            $params['date'] = $date;
            $filename .= '-until-' . $date;
        }

        $statement = $this->InfoSheet->getConnection()->prepare($query);
        $statement->execute($params);
        $records = $statement->fetchAll('assoc');
        if (empty($records)) {
            throw new NotFoundException('no repair data found');
        }

        $writer = Writer::createFromString();
        $writer->insertOne(array_keys($records[0]));
        $id = 1;
        foreach($records as &$record) {
            $record['id'] = 'anstiftung_' . $id;
            $id++;
        }
        $writer->insertAll($records);

        // force download
        $this->RequestHandler->renderAs(
            $this,
            'csv',
            [
                'charset' => 'UTF-8'
            ],
        );
        $this->disableAutoRender();

        $response = $this->response;
        $response = $response->withStringBody($writer->toString());
        $response = $response->withDownload($filename . '.csv');

        return $response;

    }

    public function download($workshopUid, $year=null) {

        $this->Workshop = $this->getTableLocator()->get('Workshops');
        $workshop = $this->Workshop->find('all', [
            'conditions' => [
                'Workshops.uid' => $workshopUid
            ]
        ])->first();

        if (empty($workshop)) {
            throw new NotFoundException('workshop not found');
        }

        $query = file_get_contents(ROOT . DS . 'config' . DS. 'sql' . DS . 'repair-data-export-csv.sql');
        $params = [
            'workshopUid' => $workshopUid
        ];
        $filename = 'Laufzettel-Download-' . StringComponent::slugifyAndKeepCase($workshop->name);
        if (in_array($year, Configure::read('AppConfig.timeHelper')->getAllYearsUntilThisYear(date('Y'), 2010))) {
            $query = preg_replace('/WHERE 1/', 'WHERE 1 AND DATE_FORMAT(e.datumstart, \'%Y\') = :year', $query);
            $params['year'] = $year;
            $filename .= '-' . $year;
        }

        $statement = $this->InfoSheet->getConnection()->prepare($query);
        $statement->execute($params);
        $records = $statement->fetchAll('assoc');
        if (empty($records)) {
            throw new NotFoundException('info sheets not found');
        }

        $writer = Writer::createFromString();
        $writer->insertOne(array_keys($records[0]));
        $writer->insertAll($records);

        // force download
        $this->RequestHandler->renderAs(
            $this,
            'csv',
            [
                'charset' => 'UTF-8'
            ],
        );
        $this->disableAutoRender();

        $response = $this->response;
        $response = $response->withStringBody($writer->toString());
        $response = $response->withDownload($filename . '.csv');

        return $response;
    }

    public function delete($infoSheetUid)
    {
        if ($infoSheetUid === null) {
            throw new NotFoundException;
        }

        $infoSheet = $this->InfoSheet->find('all', [
            'conditions' => [
                'InfoSheets.uid' => $infoSheetUid,
                'InfoSheets.status >= ' . APP_DELETED
            ]
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

    public function add($eventUid)
    {

        if ($eventUid === null) {
            throw new NotFoundException;
        }

        $this->Event = $this->getTableLocator()->get('Events');

        $this->Event->getAssociation('Workshops')->setConditions(['Workshops.status > ' . APP_DELETED]);
        $event = $this->Event->find('all', [
            'conditions' => [
                'Events.uid' => $eventUid
            ],
            'contain' => [
                'Workshops'
            ]
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

    public function edit($infoSheetUid)
    {

        if ($infoSheetUid === null) {
            throw new NotFoundException;
        }

        $infoSheet = $this->InfoSheet->find('all', [
            'conditions' => [
                'InfoSheets.uid' => $infoSheetUid,
                'InfoSheets.status >= ' . APP_DELETED
            ],
            'contain' => [
                'Events.Workshops',
                'Categories',
                'FormFieldOptions'
            ]
        ])->first();

        if (empty($infoSheet)) {
            throw new NotFoundException;
        }

        $this->setIsCurrentlyUpdated($infoSheet->uid);
        $this->set('metaTags', ['title' => 'Laufzettel bearbeiten']);
        $this->set('editFormUrl', Configure::read('AppConfig.htmlHelper')->urlInfoSheetEdit($infoSheet->uid));
        $this->_edit($infoSheet, true);
    }

    private function _edit($infoSheet, $isEditMode)
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

        $defectFoundFormField = $this->FormField->getForForm(2);
        $this->set('defectFoundFormField', $defectFoundFormField);

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

            $patchedEntity = $this->InfoSheet->getPatchedEntityForAdminEdit($infoSheet, $this->request->getData(), $this->useDefaultValidation);

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
                            'status' => $this->AppAuth->isAdmin() ? APP_ON : APP_OFF,
                            'owner' => $this->AppAuth->getUserUid()
                        ]
                    ));
                    $entity->category_id = $category->id;
                }

                if ($patchedEntity->brand_id == -1) {
                    $brand = $this->Brand->save(
                        $this->Brand->newEntity(
                            [
                                'name' => $patchedEntity->new_brand_name,
                                'status' => $this->AppAuth->isAdmin() ? APP_ON : APP_OFF,
                                'owner' => $this->AppAuth->getUserUid()
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