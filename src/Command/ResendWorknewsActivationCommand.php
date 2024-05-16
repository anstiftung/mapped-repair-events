<?php

namespace App\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use App\Model\Entity\Worknews;
use Cake\I18n\DateTime;
use Cake\Mailer\Mailer;

class ResendWorknewsActivationCommand extends Command
{

    public function execute(Arguments $args, ConsoleIo $io)
    {
        $worknewsTable = $this->getTableLocator()->get('Worknews');
        
        $query = $worknewsTable->find();
        $query->where([
                $worknewsTable->aliasField('confirm !=') => Worknews::STATUS_OK,
                $worknewsTable->aliasField('activation_email_resent IS NULL'),
                'TIMESTAMPDIFF(MINUTE, Worknews.created, NOW()) > 24 * 60', // es müssen mindestens 24 Stunden vergangen sein, bevor der Aktivierungslink erneut versendet wird
            ])
            ->contain('Workshops')
            ->orderBy(['Worknews.created' => 'DESC']);

        $i = 0;
        $email = new Mailer('default');
        
        foreach($query as $worknews) {

            $io->out($worknews->created->i18nFormat('dd.MM.yyyy HH:mm:ss') . ': ' . $worknews->email);
            $worknews->activation_email_resent = DateTime::now();
            $worknewsTable->save($worknews);

            $email->viewBuilder()->setTemplate('activate_worknews');
            $email->setSubject(__('Please activate your worknews subscription'))
                ->setViewVars([
                    'workshop' => $worknews->workshop,
                    'confirmationCode' => $worknews->confirm,
                    'unsubscribeCode' => $worknews->unsub,
            ])->setTo($worknews->email);
            $email->send();

            $i++;
        }

        $io->out($i . ' worknews activation emails resent.');

        return static::CODE_SUCCESS;


    }

}
