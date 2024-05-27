<?php

namespace App\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\CommandRunner;
use App\Application;

class StartQueueCommand extends Command
{

    public function execute(Arguments $args, ConsoleIo $io)
    {

        $runner = new CommandRunner(new Application('config'), 'cake');
        $runner->run(['cake', 'queue', 'worker', '-q']);
        
        return static::CODE_SUCCESS;

    }

}
