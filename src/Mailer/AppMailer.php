<?php
declare(strict_types=1);

namespace App\Mailer;

use Cake\Mailer\Mailer;
use Cake\Datasource\FactoryLocator;
use Cake\Mailer\Message;

class AppMailer extends Mailer
{

    public function addToQueue(): void
    {

        $this->render();

        // due to queue_jobs.text field datatype "mediumtext" the limit of emails is 16MB (including attachments)
        $queuedJobs = FactoryLocator::get('Table')->get('Queue.QueuedJobs');
        $queuedJobs->createJob('Queue.Email', [
            'class' => Message::class,
            'settings' => $this->getMessage()->__serialize(),
            'serialized' => true,
        ]);

    }

}
