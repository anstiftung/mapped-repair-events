<?php

namespace App\Controller;

use Cake\Core\Configure;
use Cake\Database\Query;
use App\Model\Entity\Funding;

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

        /*
        $workshops->where([
            'Workshops.url' => 'umweltzentrum-stockach-e-v',
        ]);
        */

        $workshopsWithFundingAllowed = [];
        $workshopsWithFundingNotAllowed = [];
        if ($this->isAdmin()) {
            foreach ($workshops as $workshop) {
                if ($workshop->funding_is_allowed) {
                    $workshopsWithFundingAllowed[] = $workshop;
                } else {
                    $workshopsWithFundingNotAllowed[] = $workshop;
                }
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
        if (empty($funding)) {
            $errors[] = 'Der Förderantrag wurde bereits von einem anderen Organisator erstellt.';
        }
        if (!empty($funding) && $funding->workshop->status == APP_DELETED) {
            $errors[] = 'Die Initiative ist gelöscht.';
        }
        return $errors;
        
    }

    public function edit() {

        $workshopUid = (int) $this->getRequest()->getParam('workshopUid');

        $fundingsTable = $this->getTableLocator()->get('Fundings');
        $funding = $fundingsTable->findOrCreateCustom($workshopUid);

        $this->setReferer();
        
        $workshopsTable = $this->getTableLocator()->get('Workshops');
        $workshop = $workshopsTable->find()->where(['uid' => $workshopUid])->contain($this->getContain())->first();
        $errors = $this->getBasicErrorMessages($funding);
        if (!$workshop->funding_is_allowed) {
            $errors[] = 'Die Initiative erfüllt die Voraussetzungen für eine Förderung nicht.';
        }

        if (count($errors) > 1) {
            $this->AppFlash->setFlashError(implode(' ', $errors));
            return $this->redirect(Configure::read('AppConfig.htmlHelper')->urlFundings());
        }

        if (!empty($this->request->getData())) {
            $assocations = ['Workshops', 'OwnerUsers'];
            if (!array_key_exists('verified_fields', $this->request->getData('Fundings'))) {
                $this->request = $this->request->withData('Fundings.verified_fields', []);
            }
            $patchedEntity = $fundingsTable->patchEntity($funding, $this->request->getData(), [
                'associated' => $assocations,
            ]);
            $errors = $patchedEntity->getErrors();

            if (empty($errors)) {
                $entity = $this->stripTagsFromFields($patchedEntity, 'Workshop');
                if ($fundingsTable->save($entity, ['associated' => $assocations])) {
                    $this->AppFlash->setFlashMessage('Förderantrag erfolgreich gespeichert.');
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