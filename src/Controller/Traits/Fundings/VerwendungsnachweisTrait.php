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

            $fundingreceiptlistsTable = $this->getTableLocator()->get('Fundingreceiptlists');

            // DELETE fundingreceiptlist
            $flashMessages = ['Der Verwendungsnachweis wurde erfolgreich zwischengespeichert.'];
            $deletedCount = 0;
            foreach($patchedEntity->fundingreceiptlists as $index => $fundingreceiptlist) {
                if ($fundingreceiptlist->delete) {
                    $deletedCount++;
                    $fundingreceiptlistsTable->delete($fundingreceiptlist);
                    unset($patchedEntity->fundingreceiptlists[$index]);
                    $this->request = $this->request->withoutData('Fundings.fundingreceiptlists.' . $index);
                }
            }
            if ($deletedCount > 0) {
                $flashMessages[] = $deletedCount . ' Beleg(e) wurde(n) erfolgreich gelöscht.';
            }
            $fundingreceiptlistsTable->saveMany($patchedEntity->fundingreceiptlists);

            // ADD fundingreceiptlist
            if (!empty($this->request->getData('add_receipt'))) {
                $newFundingreceiptlistEntity = $fundingreceiptlistsTable->createNewUnvalidatedEmptyEntity($funding->uid);
                $fundingreceiptlistsTable->save($newFundingreceiptlistEntity);
                $fundingreceiptlistsCount = $fundingreceiptlistsTable->getCountForFunding($funding->uid);
                $this->request = $this->request->withData('Fundings.fundingreceiptlists.' . ($fundingreceiptlistsCount -1), $newFundingreceiptlistEntity->toArray());
                $flashMessages[] = 'Ein neuer Beleg wurde erstellt.';
            }

            $fundingusageproofsTable = $this->getTableLocator()->get('Fundingusageproofs');
            $fundingusageproofsTable->save($patchedEntity->fundingusageproof);

            $this->AppFlash->setFlashMessage(join('<br />', $flashMessages));
            $patchedEntity = $this->patchFunding($funding, $associations);
        }

        $this->set('metaTags', [
            'title' => 'Verwendungsnachweis für Förderantrag (UID: ' . $funding->uid . ')',
        ]);
        $this->set('funding', $funding);

        return null;
    }

}
