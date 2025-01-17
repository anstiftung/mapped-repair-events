<?php
declare(strict_types=1);

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

        $runner = new CommandRunner(new Application(ROOT . DS . 'config'), 'cake');
        $runner->run(['cake', 'queue', 'run', '-q']);

        return static::CODE_SUCCESS;

    }

}
