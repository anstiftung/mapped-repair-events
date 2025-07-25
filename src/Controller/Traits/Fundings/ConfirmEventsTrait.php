<?php
declare(strict_types=1);

namespace App\Controller\Traits\Fundings;

use Cake\Http\Exception\NotFoundException;
use Cake\Http\Response;
use Cake\Core\Configure;
use Cake\Log\Log;

trait ConfirmEventsTrait {

    public function confirmEvents(): ?Response
    {

        $fundingUid = (int) $this->getRequest()->getParam('uid');
        
        $fundingsTable = $this->getTableLocator()->get('Fundings');
        $funding = $fundingsTable->find()->where([
            $fundingsTable->aliasField('uid') => $fundingUid,
        ])
        ->contain([
            'Workshops' => [
                'FundingAllFutureEvents' => [
                    'sort' => [
                        'FundingAllFutureEvents.datumstart' => 'ASC'
                    ],
                    'Fundingconfirmedevents',
                ],
            ],
        ])
        ->first();

        if (empty($funding)) {
            throw new NotFoundException;
        }

        $this->setReferer();

        if (!empty($this->request->getData())) {

            $confirmedEventUids = $this->getCleanedEventUids($funding->workshop_uid);

            /** @var \App\Model\Table\FundingconfirmedeventsTable */
            $fundingconfirmedeventsTable = $this->fetchTable('Fundingconfirmedevents');
            $fundingconfirmedeventsTable->deleteAll([
                'funding_uid' => $funding->uid,
            ]);

            $newEntities = $fundingconfirmedeventsTable->newEntities(array_map(function($eventUid) use ($funding) {
                return [
                    'event_uid' => $eventUid,
                    'funding_uid' => $funding->uid,
                ];
            }, $confirmedEventUids));
            if (!empty($newEntities)) {
                $fundingconfirmedeventsTable->saveMany($newEntities);
            }
            $this->AppFlash->setFlashMessage('Die Veranstaltungen wurden erfolgreich bestätigt.');
            return $this->redirect(Configure::read('AppConfig.htmlHelper')->urlFundings());
        }

        $this->set('metaTags', [
            'title' => 'Veranstaltungen bestätigen für Förderantrag (UID: ' . $funding->uid . ')',
        ]);
        $this->set('funding', $funding);

        return null;
    }

    private function getCleanedEventUids(int $workshopUid): array {
        $confirmedevents = $this->request->getData('confirmedevents');
        $confirmedevents = array_filter($confirmedevents, function($value) {
            return $value > 0;
        });
        $confirmedevents = array_values($confirmedevents);

        $eventsTable = $this->fetchTable('Events');
        $events = $eventsTable->find()
            ->where(['uid IN' => $confirmedevents,
            'workshop_uid' => $workshopUid])
            ->all();
        return $events->extract('uid')->toArray();
    }

}
