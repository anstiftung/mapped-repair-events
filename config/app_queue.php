<?php
declare(strict_types=1);

$connection = 'default';
$exitwhennothingtodo = false;
if (PHP_SAPI == 'cli' && $_SERVER['argv'][0] && preg_match('/phpunit/', $_SERVER['argv'][0])) {
    $connection = 'test';
    $exitwhennothingtodo = true;
}

return [
    'Queue' => [
        'maxworkers' => 3,
        'defaultworkerretries' => 2,
        'workertimeout' => 360, // cron starts new worker every 5 min (=300 sec), overlap
        'workermaxruntime' => 360,
        'sleeptime' => 5,
        'cleanuptimeout' => 518400, // 6 days
        'gcprob' => 100,
        'connection' => $connection,
        'exitwhennothingtodo' => $exitwhennothingtodo,
    ],
];

?>