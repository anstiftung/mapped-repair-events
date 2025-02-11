<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Core\Configure;
use App\Model\Entity\Funding;
use Cake\Utility\Inflector;
use App\Model\Entity\Fundingupload;
use Cake\Http\Exception\NotFoundException;
use App\Controller\Component\StringComponent;
use Cake\I18n\DateTime;
use App\Services\PdfWriter\FoerderbewilligungPdfWriterService;
use App\Services\PdfWriter\FoerderantragPdfWriterService;
use App\Mailer\AppMailer;
use Cake\Http\Response;

class FundingsController extends AppController
{

    public function index(): void
    {

        $this->set('metaTags', [
            'title' => 'Förderantrag',
        ]);

        if (Configure::read('debug') && $this->isAdmin()) {
            ini_set('memory_limit', '712M');
        }

        $workshopsTable = $this->getTableLocator()->get('Workshops');
        if ($this->isAdmin()) {
            $workshops = $workshopsTable->getWorkshopsWithUsers(APP_OFF, $workshopsTable->getFundingContain());
        } else {
            $workshops = $workshopsTable->getWorkshopsForAssociatedUser($this->loggedUser->uid, APP_OFF, $workshopsTable->getFundingContain());
        }

        foreach ($workshops as $workshop) {
            $workshop->funding_exists = !empty($workshop->workshop_funding);
            $workshop->funding_created_by_different_owner = $workshop->funding_exists && $workshop->workshop_funding->owner != $this->loggedUser->uid;
            if (!empty($workshop->workshop_funding->owner_user)) {
                $workshop->workshop_funding->owner_user->revertPrivatizeData();
            }
            $orgaTeam = $workshopsTable->getOrgaTeam($workshop);
            $orgaTeamReverted = [];
            if (!empty($orgaTeam)) {
                foreach($orgaTeam as $orgaUser) {
                    $orgaUser->revertPrivatizeData();
                    $orgaTeamReverted[] = $orgaUser;
                }
            }
            $workshop->orga_team = $orgaTeamReverted;
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

    private function getBasicErrorMessages($funding): array
    {
        $errors = ['Zugriff auf diese Seite nicht möglich.'];
        if (!empty($funding) && $funding->workshop->status == APP_DELETED) {
            $errors[] = 'Die Initiative ist gelöscht.';
        }
        return $errors;

    }

    private function createdByOtherOwnerCheck($workshopUid): string
    {
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

    public function edit(): ?Response
    {

        $workshopUid = (int) $this->getRequest()->getParam('uid');

        $fundingFinished = Configure::read('AppConfig.timeHelper')->isFundingFinished();
        if ($fundingFinished) {
            $this->AppFlash->setFlashError('Die Antragstellung ist nicht mehr möglich.');
            return $this->redirect(Configure::read('AppConfig.htmlHelper')->urlFundings());
        }

        $createdByOtherOwnerCheckMessage = $this->createdByOtherOwnerCheck($workshopUid);
        if ($createdByOtherOwnerCheckMessage != '') {
            $this->AppFlash->setFlashError($createdByOtherOwnerCheckMessage);
            return $this->redirect(Configure::read('AppConfig.htmlHelper')->urlFundings());
        }

        $fundingsTable = $this->getTableLocator()->get('Fundings');
        $funding = $fundingsTable->findOrCreateCustom($workshopUid);

        if ($funding->is_submitted) {
            $this->AppFlash->setFlashError('Der Förderantrag wurde bereits eingereicht und kann nicht mehr bearbeitet werden.');
            return $this->redirect(Configure::read('AppConfig.htmlHelper')->urlFundings());
        }

        $this->setReferer();

        $basicErrors = $this->getBasicErrorMessages($funding);
        if (!$funding->workshop->funding_is_allowed) {
            $basicErrors[] = 'Die Initiative erfüllt die Voraussetzungen für eine Förderung nicht.';
        }

        if (count($basicErrors) > 1) {
            $this->AppFlash->setFlashError(implode(' ', $basicErrors));
            return $this->redirect(Configure::read('AppConfig.htmlHelper')->urlFundings());
        }

        if (!empty($this->request->getData())) {

            $associations = ['Workshops', 'OwnerUsers', 'Fundingdatas', 'Fundingsupporters', 'FundinguploadsActivityProofs', 'FundinguploadsFreistellungsbescheids', 'Fundingbudgetplans'];
            $associationsWithoutValidation = $this->removeValidationFromAssociations($associations);
            $singularizedAssociations = array_map(function($association) {
                return Inflector::singularize(Inflector::tableize($association));
            }, $associations);
            $associations['OwnerUsers'] = ['validate' => 'funding'];

            foreach($singularizedAssociations as $association) {
                $dataKey = 'Fundings.'.$association;
                if (in_array($dataKey, ['Fundings.workshop', 'Fundings.owner_user'])) {
                    // cleaning cannot be done in entity because of allowedBasicHtmlFields
                    foreach ($this->request->getData($dataKey) as $field => $value) {
                        $cleanedValue = strip_tags($value);
                        $cleanedValue = StringComponent::removeEmojis($cleanedValue);
                        $this->request = $this->request->withData($dataKey . '.' . $field, $cleanedValue);
                    }
                }
            }

            if (!array_key_exists('verified_fields', $this->request->getData('Fundings'))) {
                $this->request = $this->request->withData('Fundings.verified_fields', []);
            }

            $addressStringOwnerUser = $this->request->getData('Fundings.owner_user.zip') . ' ' . $this->request->getData('Fundings.owner_user.city') . ', ' . $this->request->getData('Fundings.owner_user.country_code');
            $this->updateCoordinates($funding->owner_user, 'owner_user', $addressStringOwnerUser);

            $addressStringWorkshop = $this->request->getData('Fundings.workshop.street') . ', ' . $this->request->getData('Fundings.workshop.zip') . ' ' . $this->request->getData('Fundings.workshop.city') . ', ' . $this->request->getData('Fundings.workshop.country_code');
            $this->updateCoordinates($funding->workshop, 'workshop', $addressStringWorkshop);

            $patchedEntity = $this->patchFunding($funding, $associations);
            $errors = $patchedEntity->getErrors();

            $newFundinguploads = $this->handleNewFundinguploads($funding, $associations, $patchedEntity, Fundingupload::TYPE_MAP_STEP_1);
            $this->handleDeleteFundinguploads($funding, $associations, $patchedEntity, $newFundinguploads, Fundingupload::TYPE_MAP_STEP_1);

            if (!empty($errors)) {
                $patchedEntity = $this->getPatchedFundingForValidFields($errors, $workshopUid, $associationsWithoutValidation);
                $funding->verified_fields = $patchedEntity->verified_fields;
            }

            // remove all invalid fundingbudgetplans in order to avoid saving nothing
            foreach($patchedEntity->fundingbudgetplans as $index => $fundingbudgetplan) {
                if ($fundingbudgetplan->hasErrors()) {
                    unset($patchedEntity->fundingbudgetplans[$index]);
                }
            }

            $patchedEntity->owner_user->private = $this->updatePrivateFieldsForFieldsThatAreNotRequiredInUserProfile($patchedEntity->owner_user->private);
            $patchedEntity->modified = DateTime::now();
            $fundingsTable->save($patchedEntity, ['associated' => $associationsWithoutValidation]);
            $this->AppFlash->setFlashMessage('Der Förderantrag wurde erfolgreich zwischengespeichert.');

            $patchedEntity = $this->handleUpdateNewFundinguploadsWithIds($funding, $associations, $patchedEntity, Fundingupload::TYPE_MAP_STEP_1);

            if (!$patchedEntity->hasErrors() && $funding->is_submittable && !empty($this->request->getData('submit_funding'))) {
                $this->submitFunding($funding);
                $this->AppFlash->setFlashMessage('Der Förderantrag wurde erfolgreich eingereicht und bewilligt.');
                return $this->redirect(Configure::read('AppConfig.htmlHelper')->urlFundings());
            }

        }

        $this->set('metaTags', [
            'title' => 'Förderantrag (UID: ' . $funding->uid . ')',
        ]);
        $this->set('funding', $funding);
        return null;

    }

    private function submitFunding($funding): void
    {

        $timestamp = DateTime::now();

        try {

            $funding->owner_user->revertPrivatizeData();

            $email = new AppMailer();
            $email->viewBuilder()->setTemplate('fundings/funding_submitted');
            $email->setTo([
                $funding->owner_user->email,
                $funding->fundingsupporter->contact_email,
            ]);
            $email->setSubject('Förderantrag erfolgreich eingereicht und bewilligt (UID: ' . $funding->uid . ')');
            $email->setViewVars([
                'data' => $funding->owner_user,
            ]);
    
            $pdfWriterServiceA = new FoerderbewilligungPdfWriterService();
            $pdfWriterServiceA->prepareAndSetData($funding->uid, $timestamp);
            $pdfWriterServiceA->writeFile();
            $email->addAttachments([$pdfWriterServiceA->getFilenameWithoutPath() => [
                'data' => file_get_contents($pdfWriterServiceA->getFilename()),
                'mimetype' => 'application/pdf',
            ]]);
    
            $pdfWriterServiceB = new FoerderantragPdfWriterService();
            $pdfWriterServiceB->prepareAndSetData($funding->uid, $timestamp);
            $pdfWriterServiceB->writeFile();
            $email->addAttachments([$pdfWriterServiceB->getFilenameWithoutPath() => [
                'data' => file_get_contents($pdfWriterServiceB->getFilename()),
                'mimetype' => 'application/pdf',
            ]]);
    
            $email->addAttachments(['Foerderrichtlinie-anstiftung-bmuv-nov-2024.pdf' => [
                'data' => file_get_contents(WWW_ROOT . 'files/foerderung/Foerderrichtlinie-anstiftung-bmuv-nov-2024.pdf'),
                'mimetype' => 'application/pdf',
            ]]);
    
            $email->addToQueue();
    
        } catch (\Exception $e) {
            $this->AppFlash->setFlashError('Fehler beim Versenden der E-Mail.');
        }

        $fundingsTable = $this->getTableLocator()->get('Fundings');
        $funding->submit_date = $timestamp;;
        $fundingsTable->save($funding);

    }

    private function handleUpdateNewFundinguploadsWithIds($funding, $associations, $patchedEntity, $uploadTypes): Funding
    {
        foreach($uploadTypes as $uploadTypeId => $uploadType) {
            if (!empty($this->request->getData('Fundings.fundinguploads_' . $uploadType))) {
                // patch id for new fundinguploads
                $fundinguploadsFromDatabase = $this->getTableLocator()->get('Fundinguploads')->find()->where([
                    'Fundinguploads.funding_uid' => $funding->uid,
                ])->toArray();
                $updatedFundinguploads = [];
                foreach($this->request->getData('Fundings.fundinguploads_' . $uploadType) as $fundingupload) {
                    foreach($fundinguploadsFromDatabase as $fundinguploadFromDatabaseEntity) {
                        if ($fundingupload['filename'] == $fundinguploadFromDatabaseEntity->filename) {
                            $fundingupload['id'] = $fundinguploadFromDatabaseEntity->id;
                            $updatedFundinguploads[] = $fundingupload;
                        }
                    }
                }
                $this->request = $this->request->withData('Fundings.fundinguploads_' . $uploadType, $updatedFundinguploads);
                $patchedEntity = $this->patchFunding($funding, $associations);
            }
        }
        return $patchedEntity;
    }

    private function handleDeleteFundinguploads($funding, $associations, $patchedEntity, $newFundinguploads, $uploadTypes): void
    {

        foreach($uploadTypes as $uploadTypeId => $uploadType) {
            $deleteFundinguploads = $this->request->getData('Fundings.delete_fundinguploads_' . $uploadType);
            if (!empty($deleteFundinguploads)) {
                $remainingFundinguploads = $this->request->getData('Fundings.fundinguploads_' . $uploadType) ?? [];
                foreach($deleteFundinguploads as $fundinguploadId) {
                    $fundinguploadsTable = $this->getTableLocator()->get('Fundinguploads');
                    $fundingupload = $fundinguploadsTable->find()->where([
                        $fundinguploadsTable->aliasField('id') => $fundinguploadId,
                        $fundinguploadsTable->aliasField('funding_uid') => $funding->uid,
                        $fundinguploadsTable->aliasField('owner') => $this->loggedUser->uid,
                        ])->first();
                    if (!empty($fundingupload)) {
                        $fundinguploadsTable->delete($fundingupload);
                        if (file_exists($fundingupload->full_path)) {
                            unlink($fundingupload->full_path);
                        }
                        $remainingFundinguploads = array_filter($remainingFundinguploads, function($fundingupload) use ($fundinguploadId) {
                            return $fundingupload['id'] != $fundinguploadId;
                        });
                    }
                    $this->request = $this->request->withData('Fundings.fundinguploads_' . $uploadType, $remainingFundinguploads);
                    $patchedEntity = $this->patchFunding($funding, $associations);
                }
            }

            $fundinguploadsErrors = $patchedEntity->getError('fundinguploads_' . $uploadType);
            if (!empty($fundinguploadsErrors)) {
                $patchedEntity->setError('files_fundinguploads_' . $uploadType . '[]', $fundinguploadsErrors);
            } else {
                $patchedEntity = $this->patchFundingStatusIfNewUploadWasUploadedOrDeleted($newFundinguploads[$uploadType] ?? [], $patchedEntity, $uploadType, $deleteFundinguploads);
            }

        }

    }

    private function handleNewFundinguploads($funding, $associations, $patchedEntity, $uploadTypes): array
    {
        $newFundinguploads = [];
        foreach($uploadTypes as $uploadTypeId => $uploadType) {
            $filesFundinguploadsErrors = $patchedEntity->getError('files_fundinguploads_' . $uploadType);
            if (!empty($filesFundinguploadsErrors)) {
                $patchedEntity->setError('files_fundinguploads_' . $uploadType . '[]', $filesFundinguploadsErrors);
            } else {
                $filesFileuploads = $this->request->getData('Fundings.files_fundinguploads_' . $uploadType);
                if (!empty($filesFileuploads)) {
                    foreach ($filesFileuploads as $fileupload) {
                        if ($fileupload->getError() !== UPLOAD_ERR_OK) {
                            continue;
                        }
                        $filename = $fileupload->getClientFilename();
                        $filename =  StringComponent::slugifyAndKeepCase(pathinfo($filename, PATHINFO_FILENAME)) . '_' . bin2hex(random_bytes(5)) . '.' . pathinfo($filename, PATHINFO_EXTENSION);
                        $newFundinguploads[$uploadType][] = [
                            'filename' => $filename,
                            'funding_uid' => $funding->uid,
                            'type' => $uploadTypeId,
                            'owner' => $this->loggedUser->uid,
                        ];
                        $filePath = Fundingupload::UPLOAD_PATH . $funding->uid . DS . $filename;
                        if (!is_dir(dirname($filePath))) {
                            mkdir(dirname($filePath), 0777, true);
                        }
                        $fileupload->moveTo($filePath);
                    }
                    
                    if (!empty($newFundinguploads[$uploadType])) {
                        $this->request = $this->request->withData('Fundings.fundinguploads_' . $uploadType, array_merge($this->request->getData('Fundings.fundinguploads_' . $uploadType) ?? [], $newFundinguploads[$uploadType]));
                    }
                    $patchedEntity = $this->patchFunding($funding, $associations);
                }
            }
        }
        return $newFundinguploads;
    }

    private function patchFunding($funding, $associations): Funding
    {
        $fundingsTable = $this->getTableLocator()->get('Fundings');
        $patchedEntity = $fundingsTable->patchEntity($funding, $this->request->getData(), [
            'associated' => $associations,
        ]);
        return $patchedEntity;
    }

    private function updatePrivateFieldsForFieldsThatAreNotRequiredInUserProfile($privateFields): string
    {
        $fields = ['street', 'city', 'phone'];
        $existingArray = array_map('trim', explode(',', $privateFields));
        $updatedArray = array_unique(array_merge($existingArray, $fields));
        return implode(',', $updatedArray);
    }

    private function patchFundingStatusIfNewUploadWasUploadedOrDeleted($newFundinguploads, $patchedEntity, $uploadType, $deleteFundinguploads): Funding
    {
        if (!empty($newFundinguploads) || !empty($deleteFundinguploads)) {
            $newStatus = Funding::STATUS_PENDING;
            $singularizedUploadType = Inflector::singularize($uploadType);
            if (!empty($deleteFundinguploads)) {
                $noUploadAvailable = count($patchedEntity->{'fundinguploads_' . $uploadType}) == 0;
                if ($noUploadAvailable) {
                    $newStatus = Funding::STATUS_UPLOAD_MISSING;
                }
            }
            $this->request = $this->request->withData('Fundings.' . $singularizedUploadType . '_status', $newStatus);
            $patchedEntity->{$singularizedUploadType . '_status'} = $newStatus;
        }
        return $patchedEntity;
    }

    private function updateCoordinates($entity, $index, $addressString): void
    {
        if (!$entity->use_custom_coordinates) {
            $geoData = $this->geoService->getGeoDataByAddress($addressString);
            $this->request = $this->request->withData('Fundings.'.$index.'.lat', $geoData['lat']);
            $this->request = $this->request->withData('Fundings.'.$index.'.lng', $geoData['lng']);
            $this->request = $this->request->withData('Fundings.'.$index.'.province_id', $geoData['provinceId'] ?? 0);
        }
    }

    public function delete(): void
    {
        $fundingUid = (int) $this->request->getParam('uid');
        $fundingsTable = $this->getTableLocator()->get('Fundings');
        $fundingsTable->deleteCustom($fundingUid);
        $this->AppFlash->setFlashMessage('Der Förderantrag wurde erfolgreich gelöscht.');
        $this->redirect(Configure::read('AppConfig.htmlHelper')->urlFundings());
    }

    private function getPatchedFundingForValidFields($errors, $workshopUid, $associationsWithoutValidation): Funding
    {
        $data = $this->request->getData();
        $verifiedFieldsWithErrors = [];
        foreach ($errors as $entity => $fieldErrors) {
            if (!in_array($entity, ['workshop', 'owner_user', 'fundingsupporter'])) {
                continue;
            }
            $fieldNames = array_keys($fieldErrors);
            foreach($fieldNames as $fieldName) {
                $verifiedFieldsWithErrors[] = Inflector::dasherize('fundings-' . $entity . '-' . $fieldName);
                unset($data['Fundings'][$entity][$fieldName]);
            }
        }
        // never save "verified" if field has error
        $verifiedFields = $data['Fundings']['verified_fields'];
        $patchedVerifiedFieldsWithoutErrorFields = array_diff($verifiedFields, $verifiedFieldsWithErrors);
        $data['Fundings']['verified_fields'] = array_values($patchedVerifiedFieldsWithoutErrorFields);

        $fundingsTable = $this->getTableLocator()->get('Fundings');
        $fundingForSaving = $fundingsTable->findOrCreateCustom($workshopUid);
        $patchedEntity = $fundingsTable->patchEntity($fundingForSaving, $data, [
            'associated' => $associationsWithoutValidation,
        ]);
        return $patchedEntity;
    }

    private function removeValidationFromAssociations($associations): array
    {
        $result = array_map(function($association) {
            return ['validate' => false];
        }, array_flip($associations));
        // some association's data should not be saved if invalid
        foreach($result as $entity => $value) {
            if (in_array($entity, ['Fundingbudgetplans'])) {
                $result[$entity] = ['validate' => 'default'];
            }
        }
        return $result;
    }

    public function uploadZuwendungsbestaetigung():void
    {

        $fundingsTable = $this->getTableLocator()->get('Fundings');
        $fundingUid = $this->getRequest()->getParam('uid');
        $funding = $fundingsTable->getUnprivatizedFundingWithAllAssociations($fundingUid);

        if (!$funding->is_submitted) {
            throw new NotFoundException('Förderantrag (UID: '.$fundingUid.') wurde noch nicht eingereicht.');
        }

        $this->setReferer();

        if (!empty($this->request->getData())) {
            $associations = ['FundinguploadsZuwendungsbestaetigungs'];
            $patchedEntity = $this->patchFunding($funding, $associations);
            $newFundinguploads = $this->handleNewFundinguploads($funding, $associations, $patchedEntity, Fundingupload::TYPE_MAP_STEP_2);
            $this->handleDeleteFundinguploads($funding, $associations, $patchedEntity, $newFundinguploads, Fundingupload::TYPE_MAP_STEP_2);

            $patchedEntity->modified = DateTime::now();
            $fundingsTable->save($patchedEntity);
            $this->AppFlash->setFlashMessage('Die Zuwendungsbestägigung wurde erfolgreich gespeichert.');
            $patchedEntity = $this->handleUpdateNewFundinguploadsWithIds($funding, $associations, $patchedEntity, Fundingupload::TYPE_MAP_STEP_2);

        }

        $this->set('metaTags', [
            'title' => 'Upload Zuwendungsbestätigung zu Förderantrag UID: ' . $funding->uid . ')',
        ]);
        $this->set('funding', $funding);

    }

    public function download(): Response
    {

        $fundingUid = $this->getRequest()->getParam('uid');
        $type = $this->getRequest()->getParam('type');

        $fundingsTable = $this->getTableLocator()->get('Fundings');
        $funding = $fundingsTable->find()->where([
            $fundingsTable->aliasField('uid') => $fundingUid,
            $fundingsTable->aliasField('submit_date IS NOT NULL'),
        ])->first();

        if (empty($funding)) {
            throw new NotFoundException;
        }

        if ($type == 'foerderantrag') {
            $pdfWriterService = new FoerderantragPdfWriterService();
        }
        if ($type == 'foerderbewilligung') {
            $pdfWriterService = new FoerderbewilligungPdfWriterService();
        }

        if (isset($pdfWriterService)) {
            $filename = $pdfWriterService->getFilenameCustom($funding, $funding->submit_date);
            $filenameWithPath = $pdfWriterService->getUploadPath($funding->uid) . $filename;

            $response = $this->response->withFile($filenameWithPath);
            $response = $response->withHeader('Content-Disposition', 'inline; filename="' . $filename . '"');
            return $response;
        }
        
        throw new NotFoundException;

    }

    public function uploadDetail(): Response
    {

        $fundinguploadUid = $this->getRequest()->getParam('uid');
        $fundinguploadsTable = $this->getTableLocator()->get('Fundinguploads');
        $fundingupload = $fundinguploadsTable->find('all',
        conditions: [
            $fundinguploadsTable->aliasField('id') => $fundinguploadUid,
        ])->first();

        if (empty($fundingupload)) {
            throw new NotFoundException;
        }

        $response = $this->response->withFile($fundingupload->full_path);
        $response = $response->withHeader('Content-Disposition', 'inline; filename="' . $fundingupload->filename . '"');
        return $response;

    }

}