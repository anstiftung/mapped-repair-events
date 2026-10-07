<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\Core\Configure;
use Cake\Validation\Validator;
use App\Model\Entity\Worknews;
use App\Mailer\AppMailer;
use Cake\ORM\Query\SelectQuery;
use Cake\ORM\TableRegistry;
use App\Model\Entity\Workshop;
use App\Model\Entity\Event;

/**
 * @extends \App\Model\Table\AppTable<\App\Model\Entity\Worknews>
 */
class WorknewsTable extends AppTable
{

    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->setPrimaryKey('id');
        $this->belongsTo('Workshops', [
            'foreignKey' => 'workshop_uid'
        ]);
        $this->addBehavior('Timestamp');
    }

    /**
     * @param \Cake\ORM\Query\SelectQuery<\App\Model\Entity\Worknews> $query
     * @return \Cake\ORM\Query\SelectQuery<\App\Model\Entity\Worknews>
     */
    public function findWithOutOfQuotaCount(SelectQuery $query): SelectQuery
    {
        $totals = TableRegistry::getTableLocator()->get('WorknewsEmailErrors')->find('totals');
        $query->getSelectTypeMap()->addDefaults(['out_of_quota_count' => 'integer']);

        return $query->leftJoin(
            ['WorknewsEmailErrors' => $totals],
            ['WorknewsEmailErrors.email = Worknews.email'],
        )->select([
            'out_of_quota_count' => $query->expr('COALESCE(WorknewsEmailErrors.out_of_quota_count, 0)'),
        ])->enableAutoFields();
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator->email('email', true, 'Bitte trage eine gültige E-Mail-Adresse ein.');
        $validator->notEmptyString('email', 'Bitte trage deine E-Mail-Adresse ein.');
        $validator->add('email', 'unique', [
            'rule' => ['validateUnique', ['scope' => 'workshop_uid']],
            'provider' => 'table',
            'message' => 'Diese E-Mail-Adresse wird bereits verwendet.'
        ]);
        return $validator;
    }

    /** @return \Cake\ORM\Query\SelectQuery<\App\Model\Entity\Worknews> */
    public function getSubscribers(int $workshopUid): SelectQuery
    {
        $subscribers = $this->find('all',
        conditions: [
            'Worknews.workshop_uid' => $workshopUid,
            'Worknews.confirm' => Worknews::STATUS_OK,
        ],
        contain: [
            'Workshops',
        ]);
        /** @var \Cake\ORM\Query\SelectQuery<\App\Model\Entity\Worknews> $subscribers */
        return $subscribers;
    }

    /**
     * @param \Cake\ORM\Query\SelectQuery<\App\Model\Entity\Worknews> $subscribers
     * @param array<string> $dirtyFields
     * @param array<string, mixed> $originalValues
     */
    public function sendNotifications(SelectQuery $subscribers, string $subject, string $template, Workshop $workshop, Event $event, array $dirtyFields = [], array $originalValues = []): void
    {
        $email = new AppMailer();
        $email->viewBuilder()->setTemplate($template);
        foreach ($subscribers as $subscriber) {
            $email->setTo($subscriber->email)
            ->setReplyTo($workshop->email)
            ->setSubject($subject)
            ->setViewVars([
                'url' => Configure::read('AppConfig.htmlHelper')->urlEventDetail($workshop->url, $event->uid, $event->datumstart),
                'unsub' => $subscriber->unsub,
                'workshop' => $workshop,
                'event' => $event,
                'dirtyFields' => $dirtyFields,
                'originalValues' => $originalValues,
            ]);
            $email->addToQueue($subscriber->id);
        }
    }

}
?>