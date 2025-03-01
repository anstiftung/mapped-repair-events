<?php
declare(strict_types=1);
return [
    'debug' => true,
    'emailErrorLoggingEnabled' => false,
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
            'from' => [],
        ]
    ],
    'Datasources' => [
        'default' => [
            'host' => '',
            'username' => '',
            'password' => '',
            'database' => ''
        ]
    ],
    /**
     * please generate a salt: hash('sha256', Security::randomBytes(64))
     */
    'Security' => [
        'salt' => '',
    ]
];
?>
