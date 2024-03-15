<?php

namespace App\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;

class CleanWorknewsCommand extends Command
{

    public function execute(Arguments $args, ConsoleIo $io)
    {

        $worknewsTable = $this->getTableLocator()->get('Worknews');

        $worknews = $worknewsTable->find('all',
        conditions: [
            $worknewsTable->aliasField('confirm !=') => 'ok',
            'DATEDIFF(NOW(), Worknews.created) >= 30',
        ],
        order: ['Worknews.created' => 'ASC']
        );

        $i = 0;
        foreach($worknews as $w) {
            $io->out($w->created->i18nFormat('dd.MM.yyyy HH:mm:ss') . ': ' . $w->email);
            $worknewsTable->delete($w);
            $i++;
        }
        $io->out($i . ' records deleted.');

        return static::CODE_SUCCESS;

    }

}
