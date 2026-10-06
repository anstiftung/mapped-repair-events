<?php
declare(strict_types=1);

namespace App\Mailer;

use Cake\Mailer\Mailer;
use Cake\Datasource\FactoryLocator;
use Cake\Mailer\Message;

class AppMailer extends Mailer
{

    public function addToQueue(?int $worknewsId = null): void
    {

        $this->render();

        // due to queue_jobs.text field datatype "mediumtext" the limit of emails is 16MB (including attachments)
        $queuedJobs = FactoryLocator::get('Table')->get('Queue.QueuedJobs');
        /* @phpstan-ignore-next-line */
        $queuedJobs->createJob($worknewsId === null ? 'Queue.Email' : 'WorknewsEmail', [
            'class' => Message::class,
            'settings' => $this->getMessage()->__serialize(),
            'serialized' => true,
            'worknews_id' => $worknewsId,
        ]);

    }

}
