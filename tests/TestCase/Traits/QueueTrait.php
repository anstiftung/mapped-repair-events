<?php
declare(strict_types=1);

namespace App\Test\TestCase\Traits;

use Cake\Console\TestSuite\ConsoleIntegrationTestTrait;

trait QueueTrait
{

    use ConsoleIntegrationTestTrait;
    
    protected $QueuedJobs;

    protected function runAndAssertQueue()
    {
        $this->exec('queue run -q');
    }

}
