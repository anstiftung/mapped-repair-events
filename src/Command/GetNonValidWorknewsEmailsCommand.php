<?php

namespace App\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Validation\Validation as Validation;

class GetNonValidWorknewsEmailsCommand extends Command
{

    public $Worknews;

    public function execute(Arguments $args, ConsoleIo $io)
    {

        $this->Worknews = $this->getTableLocator()->get('Worknews');
        
        // find all events that start in one week
        $worknews = $this->Worknews->find('all', [
            'order' => ['Worknews.id' => 'ASC'],
        ]);

        // send notification mail to all subscribers
        $i = 0;
        foreach($worknews as $w) {
            $i++;
            $isEmailValid = Validation::email($w->email, true);
            if ($isEmailValid) {
                $io->out($i);
            } else {
                $this->log($w->email);
            }
        }

        return static::CODE_SUCCESS;

    }

}
