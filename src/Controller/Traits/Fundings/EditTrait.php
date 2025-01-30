<?php
declare(strict_types=1);

namespace App\Controller\Traits\Fundings;

use Cake\I18n\DateTime;
use Cake\Core\Configure;
use Cake\Http\Response;
use App\Model\Entity\Fundingupload;
use App\Controller\Component\StringComponent;
use Cake\Utility\Inflector;
use App\Model\Entity\Funding;
use App\Mailer\AppMailer;
use App\Services\PdfWriter\FoerderbewilligungPdfWriterService;
use App\Services\PdfWriter\FoerderantragPdfWriterService;

trait EditTrait {

    public function edit(): ?Response
    {

        $workshopUid = (int) $this->getRequest()->getParam('uid');

        $ownerCheckResult = $this->createdByOtherOwnerCheck($workshopUid);
        if ($ownerCheckResult !== false) {
            $this->AppFlash->setFlashError('Der Förderantrag wurde bereits von einem anderen Nutzer (' . $ownerCheckResult->name . ') erstellt.');
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
        
}
