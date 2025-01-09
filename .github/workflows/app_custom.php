<?php
declare(strict_types=1);

return [
    'debug' => true,
    'isApiEnabled' => true,
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
        'cookieKey' => 'e30a8116208c9558fec892ffa23e7ec95e0dd63a72833627352deee4ffdb897b',
    ],

    'AppConfig' => [
        'serverName' => 'http://localhost:8113',
    ]

];
?>
