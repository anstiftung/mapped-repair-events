<?php

namespace App\Controller;

use Cake\Core\Configure;
use Cake\Database\Query;
use App\Model\Entity\Funding;
use Cake\Utility\Inflector;

class FundingsController extends AppController
{

    private function getContain() {
        return [
            'Fundings',
            'FundingAllPastEvents' => function (Query $q) {
                return $q->select(['workshop_uid', 'count' => $q->func()->count('*')])->groupBy('workshop_uid');
            },
            'FundingAllFutureEvents' => function (Query $q) {
                return $q->select(['workshop_uid', 'count' => $q->func()->count('*')])->groupBy('workshop_uid');
            }
        ];
    }

    public function index() {

        $this->set('metaTags', [
            'title' => 'Förderantrag',
        ]);

        $workshopsTable = $this->getTableLocator()->get('Workshops');
        if ($this->isAdmin()) {
            $workshops = $workshopsTable->getWorkshopsWithUsers(APP_OFF, $this->getContain());
        } else {
            $workshops = $workshopsTable->getWorkshopsForAssociatedUser($this->loggedUser->uid, APP_OFF, $this->getContain());
        }

        $workshopsWithFundingAllowed = [];
        $workshopsWithFundingNotAllowed = [];
        foreach ($workshops as $workshop) {
            if ($workshop->funding_is_allowed) {
                $workshopsWithFundingAllowed[] = $workshop;
            } else {
                $workshopsWithFundingNotAllowed[] = $workshop;
            }
        }
        unset($workshops);

        $this->set([
            'workshopsWithFundingAllowed' => $workshopsWithFundingAllowed,
            'workshopsWithFundingNotAllowed' => $workshopsWithFundingNotAllowed,
        ]);

    }

    private function getBasicErrorMessages($funding): array {
        $errors = ['Zugriff auf diese Seite nicht möglich.'];
        if (!empty($funding) && $funding->workshop->status == APP_DELETED) {
            $errors[] = 'Die Initiative ist gelöscht.';
        }
        return $errors;

    }

    private function createdByOtherOwnerCheck($workshopUid) {
        $fundingsTable = $this->getTableLocator()->get('Fundings');
        $funding = $fundingsTable->find()->where([
            $fundingsTable->aliasField('workshop_uid') => $workshopUid,
            'NOT' => [
                $fundingsTable->aliasField('owner') => $this->loggedUser->uid,
            ],
        ])->contain(['OwnerUsers']);
        if ($funding->count() > 0) {
            $owner = $funding->first()->owner_user;
            $owner->revertPrivatizeData();
            return 'Der Förderantrag wurde bereits von einem anderen Nutzer (' . $owner->name . ') erstellt.';
        }
        return '';
    }

    public function edit() {

        $workshopUid = (int) $this->getRequest()->getParam('workshopUid');

        $createdByOtherOwnerCheckMessage = $this->createdByOtherOwnerCheck($workshopUid);
        if ($createdByOtherOwnerCheckMessage != '') {
            $this->AppFlash->setFlashError($createdByOtherOwnerCheckMessage);
            return $this->redirect(Configure::read('AppConfig.htmlHelper')->urlFundings());
        }

        $fundingsTable = $this->getTableLocator()->get('Fundings');
        $funding = $fundingsTable->findOrCreateCustom($workshopUid);

        $this->setReferer();

        $workshopsTable = $this->getTableLocator()->get('Workshops');
        $workshop = $workshopsTable->find()->where(['uid' => $workshopUid])->contain($this->getContain())->first();
        $basicErrors = $this->getBasicErrorMessages($funding);
        if (!$workshop->funding_is_allowed) {
            $basicErrors[] = 'Die Initiative erfüllt die Voraussetzungen für eine Förderung nicht.';
        }

        if (count($basicErrors) > 1) {
            $this->AppFlash->setFlashError(implode(' ', $basicErrors));
            return $this->redirect(Configure::read('AppConfig.htmlHelper')->urlFundings());
        }

        if (!empty($this->request->getData())) {

            $associations = ['Workshops', 'OwnerUsers', 'Supporters'];
            $singularizedAssociations = array_map(function($association) {
                return Inflector::singularize(Inflector::tableize($association));
            }, $associations);

            foreach($singularizedAssociations as $association) {
                $dataKey = 'Fundings.'.$association;
                foreach ($this->request->getData($dataKey) as $field => $value) {
                    $cleanedValue = strip_tags($value);
                    $this->request = $this->request->withData($dataKey . '.' . $field, $cleanedValue);
                }
            }

            if (!array_key_exists('verified_fields', $this->request->getData('Fundings'))) {
                $this->request = $this->request->withData('Fundings.verified_fields', []);
            }
            $patchedEntity = $fundingsTable->patchEntity($funding, $this->request->getData(), [
                'associated' => $associations,
            ]);
            $errors = $patchedEntity->getErrors();

            if (empty($errors)) {
                if ($fundingsTable->save($patchedEntity, ['associated' => $associations])) {
                    $this->AppFlash->setFlashMessage('Förderantrag erfolgreich gespeichert.');
                }
            } else {
                $data = $this->request->getData();
                $verifiedFieldsWithErrors = [];
                foreach ($errors as $entity => $fieldErrors) {
                    $fieldNames = array_keys($fieldErrors);
                    foreach($fieldNames as $fieldName) {
                        $verifiedFieldsWithErrors[] = Inflector::dasherize('fundings-' . $entity . '-' . $fieldName);
                        unset($data['Fundings'][$entity][$fieldName]);
                    }
                }
                // never save "verified" if field has error
                $verifiedFields = $data['Fundings']['verified_fields'];
                $patchedVerifiedFieldsWithoutErrorFields = array_diff($verifiedFields, $verifiedFieldsWithErrors);
                $data['Fundings']['verified_fields'] = $patchedVerifiedFieldsWithoutErrorFields;
                $associationsWithoutValidation = array_map(function($association) {
                    return ['validate' => false];
                }, array_flip($associations));

                $fundingForSaving = $fundingsTable->findOrCreateCustom($workshopUid);
                $patchedEntity = $fundingsTable->patchEntity($fundingForSaving, $data, [
                    'associated' => $associationsWithoutValidation,
                ]);
                if ($fundingsTable->save($patchedEntity, ['associated' => $associationsWithoutValidation])) {
                    $this->AppFlash->setFlashMessage('Alle validen Daten wurden erfolgreich gespeichert.');
                }
            }

        }

        $this->set('metaTags', [
            'title' => 'Förderantrag für "' . h($funding->workshop->name) . '"',
        ]);
        $this->set('funding', $funding);

    }

    public function uploadActivityProof() {

        $workshopUid = (int) $this->getRequest()->getParam('workshopUid');

        $createdByOtherOwnerCheckMessage = $this->createdByOtherOwnerCheck($workshopUid);
        if ($createdByOtherOwnerCheckMessage != '') {
            $this->AppFlash->setFlashError($createdByOtherOwnerCheckMessage);
            return $this->redirect(Configure::read('AppConfig.htmlHelper')->urlFundings());
        }

        $fundingsTable = $this->getTableLocator()->get('Fundings');
        $funding = $fundingsTable->findOrCreateCustom($workshopUid);

        $this->setReferer();
        $workshopsTable = $this->getTableLocator()->get('Workshops');
        $workshop = $workshopsTable->find()->where(['uid' => $workshopUid])->contain($this->getContain())->first();

        $errors = $this->getBasicErrorMessages($funding);
        if ($workshop->funding_is_allowed) {
            $errors[] = 'Die Initiative hat bereits alle Voraussetzungen für den Förderantrag erfüllt.';
        }
        if ($funding->activity_proof_filename != '') {
            $errors[] = 'Es wurde bereits ein Aktivitätsbericht hochgeladen.';
        }
        if (count($errors) > 1) {
            $this->AppFlash->setFlashError(implode(' ', $errors));
            return $this->redirect(Configure::read('AppConfig.htmlHelper')->urlFundings());
        }

        if (!empty($this->request->getData())) {

            $activityProof = $this->request->getData('Fundings.activity_proof');

            if (!in_array($activityProof->getClientMediaType(), ['application/pdf', 'image/jpeg', 'image/png'])) {
                $funding->setError('activity_proof', ['type' => 'Erlaubte Dateiformate: PDF, JPG oder PNG.']);
            }

            if ($activityProof->getSize() > 5 * 1024 * 1024) {
                $funding->setError('activity_proof', ['size' => 'Max. Dateigröße: 5 MB.']);
            }

            $fileName = $activityProof->getClientFilename();
            $funding->activity_proof_filename = $fileName;

            if ($fundingsTable->save($funding)) {

                $filePath = Funding::UPLOAD_PATH . $funding->id . DS . $fileName;
                if (!is_dir(dirname($filePath))) {
                    mkdir(dirname($filePath), 0777, true);
                }
                $activityProof->moveTo($filePath);

                $this->AppFlash->setFlashMessage(__('Der Aktivitätsnachweis wurde erfolgreich hochgeladen.'));
                $this->redirect($this->getPreparedReferer());
            } else {
                $this->AppFlash->setFlashError('Der Aktivitätsnachweis konnte nicht hochgeladen werden.');
            }

        }

        $this->set('metaTags', [
            'title' => 'Aktivitätsnachweis für "' . h($funding->workshop->name) . '" hochladen',
        ]);
        $this->set('funding', $funding);

    }

}