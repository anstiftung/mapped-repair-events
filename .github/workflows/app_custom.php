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
            'host' => 'mysql-test',
            'username' => 'my_user',
            'password' => 'password',
            'database' => 'my_app',
            'port' => 3331,
        ],
        'test' => [
            'host' => 'mysql-test',
            'username' => 'my_user',
            'password' => 'password',
            'database' => 'my_app',
            'port' => 3331,
        ]
    ],
    'Security' => [
        'salt' => '1352742f9cf63228087273ae55bdf6bb2d1a75b82c7e3c6c7023c3014c703a85',
    ],

    'AppConfig' => [
        'serverName' => 'http://localhost:8113',
    ]

];
?>
