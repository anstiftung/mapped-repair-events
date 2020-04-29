<?php
return [
    'debug' => true,
    'emailErrorLoggingEnabled' => false,
    'initiativeNameSingular' => 'Reparatur-Initiative',
    'isSsl' => false,
    'googleMapApiKey' => '',
    'EmailTransport' => [
        'default' => [
            'className' => 'Smtp',
            'host' => '',
            'port' => 465,
            'username' => '',
            'password' => '',
            'client' => null
        ],
    ],
    'Email' => [
        'default' => [
            'transport' => 'default',
            'from' => [
                'test@example.com'
            ],
            'emailFormat' => 'text',
            'charset' => 'utf-8'
        ]
    ],
    'Datasources' => [
        'default' => [
            'host' => '127.0.0.1',
            'username' => 'root',
            'password' => '',
            'database' => 'db_default'
        ],
        'test' => [
            'host' => '127.0.0.1',
            'username' => 'root',
            'password' => '',
            'database' => 'db_test'
        ],
    ],
    
    'Security' => [
        'salt' => '1352742f9cf63228087273ae55bdf6bb2d1a75b82c7e3c6c7023c3014c703a85',
    ]
];
?>
