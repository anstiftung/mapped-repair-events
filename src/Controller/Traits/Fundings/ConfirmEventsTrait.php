<?php
declare(strict_types=1);

namespace App\Controller\Traits\Fundings;

use Cake\Http\Exception\NotFoundException;
use Cake\Http\Response;
use Cake\Core\Configure;

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

            /** @var \App\Model\Table\FundingconfirmedeventsTable */
            $fundingconfirmedeventsTable = $this->fetchTable('Fundingconfirmedevents');
            $fundingconfirmedeventsTable->deleteAll([
                'funding_uid' => $funding->uid,
            ]);

            $confirmedEventUids = $this->getCleanedEventUids($funding->workshop_uid);
            if (count($confirmedEventUids) > 0) {
                $newEntities = $fundingconfirmedeventsTable->newEntities(array_map(function($eventUid) use ($funding) {
                    return [
                        'event_uid' => $eventUid,
                        'funding_uid' => $funding->uid,
                    ];
                }, $confirmedEventUids));
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

    /**
     * @return array<int>
     */
    private function getCleanedEventUids(int $workshopUid): array {
        $confirmedevents = $this->request->getData('confirmedevents');
        $confirmedevents = array_filter($confirmedevents, function($value) {
            return $value > 0;
        });
        $confirmedevents = array_values($confirmedevents);
        if (count($confirmedevents) === 0) {
            return [];
        }

        $eventsTable = $this->fetchTable('Events');
        $events = $eventsTable->find()
            ->where([
                'uid IN' => $confirmedevents,
                'workshop_uid' => $workshopUid,
            ])
            ->all();
        $events = $events->filter(function($event) {
            return $event->datumstart->isPast();
        });
        return $events->extract('uid')->toArray();
    }

}
