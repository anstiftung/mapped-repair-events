<?php
return [
    'debug' => true,
    'emailErrorLoggingEnabled' => false,
    'googleMapApiKey' => '',
    'EmailTransport' => [
        'default' => [
            'className' => 'Mail',
        ]
    ],
    'Email' => [
        'default' => [
            'transport' => 'default',
            'from' => 'you@localhost'
        ],
    ],
    'Datasources' => [
        'default' => [
            'host' => '127.0.0.1',
            'username' => 'root',
            'password' => 'password',
            'database' => 'db_default',
            'port' => '8888',
        ],
        'test' => [
            'host' => '127.0.0.1',
            'username' => 'root',
            'password' => 'password',
            'database' => 'db_test',
            'port' => '8888',
        ],
    ],

    'Security' => [
        'salt' => '1352742f9cf63228087273ae55bdf6bb2d1a75b82c7e3c6c7023c3014c703a85',
    ],

    'AppConfig' => [
        'serverName' => 'http://localhost',
    ]

];
?>
