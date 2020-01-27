<?php

namespace App\Test\TestCase\Traits;
use Cake\Filesystem\File;

trait LogFileAssertionsTrait
{
    
    public function setUp(): void
    {
        parent::setUp();
        $this->resetLogs();
    }
    
    private function getLogFile($name)
    {
        return new File(ROOT . DS . 'logs' . DS . 'cli-' . $name . '.log');
    }
    
    private function resetLogs()
    {
        $this->getLogFile('debug')->write('');
        $this->getLogFile('error')->write('');
    }
    
    public function tearDown(): void
    {
        parent::tearDown();
        $this->assertLogFilesForErrors();
    }
    
    private function assertLogFilesForErrors()
    {
        $log = $this->getLogFile('debug')->read(true, 'r');
        $log .= $this->getLogFile('error')->read(true, 'r');
        $this->assertTextNotContains('Warning', $log);
        $this->assertTextNotContains('Notice', $log);
        $this->assertTextNotContains('Error: ', $log);
    }
    
}
?>