<?php
declare(strict_types=1);

namespace App\Controller\Traits\Fundings;

use Cake\I18n\DateTime;
use Cake\Core\Configure;
use Cake\Http\Exception\NotFoundException;
use Cake\Http\Response;

trait VerwendungsnachweisTrait {

    public function verwendungsnachweis(): ?Response
    {

        $fundingUid = (int) $this->getRequest()->getParam('uid');
        
        $fundingsTable = $this->getTableLocator()->get('Fundings');
        $funding = $fundingsTable->find()->where([
            $fundingsTable->aliasField('uid') => $fundingUid,
        ])->first();

        if (empty($funding)) {
            throw new NotFoundException;
        }

        if (!$funding->is_submitted || !($funding->is_money_transferred)) {
            $this->AppFlash->setFlashError('Der Förderantrag wurde noch nicht eingereicht oder das Geld wurde noch nicht überwiesen.');
            return $this->redirect(Configure::read('AppConfig.htmlHelper')->urlFundings());
        }
        
        $ownerCheckResult = $this->createdByOtherOwnerCheck($funding->workshop_uid);
        if ($ownerCheckResult !== false) {
            $this->AppFlash->setFlashError('Der Förderantrag wurde von einem anderen Nutzer (' . $ownerCheckResult->name . ') erstellt, der Verwendungsnachweis kann daher nicht erstellt werden.');
            return $this->redirect(Configure::read('AppConfig.htmlHelper')->urlFundings());
        }

        $this->setReferer();
        $funding = $fundingsTable->findOrCreateUsageproof($fundingUid);

        if (!empty($this->request->getData())) {
            $associations = ['Fundingusageproofs', 'Fundingreceiptlists'];
            $patchedEntity = $this->patchFunding($funding, $associations);

            $associationsWithoutValidation = $this->removeValidationFromAssociations($associations);

            $patchedEntity = $this->patchFunding($funding, $associationsWithoutValidation);
            $patchedEntity->modified = DateTime::now();

            // remove $fundingreceiptlist if checkbox is set
            $fundingreceiptlistsTable = $this->getTableLocator()->get('Fundingreceiptlists');
            foreach($patchedEntity->fundingreceiptlists as $index => $fundingreceiptlist) {
                if ($fundingreceiptlist->delete) {
                    $fundingreceiptlistsTable->delete($fundingreceiptlist);
                    unset($patchedEntity->fundingreceiptlists[$index]);
                    $this->request = $this->request->withoutData('Fundings.fundingreceiptlists.' . $index);
                }
            }

            $fundingsTable->save($patchedEntity, ['associated' => $associationsWithoutValidation, 'atomic' => true]);

            $this->AppFlash->setFlashMessage('Der Verwendungsnachweis wurde erfolgreich zwischengespeichert.');
            $patchedEntity = $this->patchFunding($funding, $associations);
        }

        $this->set('metaTags', [
            'title' => 'Verwendungsnachweis für Förderantrag (UID: ' . $funding->uid . ')',
        ]);
        $this->set('funding', $funding);

        return null;
    }

}
