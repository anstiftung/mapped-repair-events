<?php

namespace App\Test\TestCase\Traits;

trait LogFileAssertionsTrait
{

    public $executeLogFileAssertions = true;

    public function setUp(): void
    {
        parent::setUp();
        $this->resetLogs();
    }

    private function getLogFile($name)
    {
        return ROOT . DS . 'logs' . DS . $name . '.log';
    }

    protected function resetLogs(): void
    {
        file_put_contents($this->getLogFile('debug'), '');
        file_put_contents($this->getLogFile('error'), '');
        file_put_contents($this->getLogFile('cli-debug'), '');
        file_put_contents($this->getLogFile('cli-error'), '');
    }

    public function tearDown(): void
    {
        parent::tearDown();
        $this->assertLogFilesForErrors();
    }

    protected function assertLogFilesForErrors()
    {
        if (!$this->executeLogFileAssertions) {
            return;
        }
        $log = file_get_contents($this->getLogFile('debug'));
        $log .= file_get_contents($this->getLogFile('error'));
        $log .= file_get_contents($this->getLogFile('cli-debug'));
        $log .= file_get_contents($this->getLogFile('cli-error'));
        $this->assertDoesNotMatchRegularExpression('/(Warning|Notice|Error)/', $log);
    }

}
?>