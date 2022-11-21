<?php
use Cake\Core\Configure;

use function PHPSTORM_META\map;

$menu = [
    [
        'name' => '&copy; ' . date('Y') . ' ' . Configure::read('AppConfig.InitiativeNamePlural'),
        'slug' => '/'
    ]
];
$menu = array_merge($menu, $this->Menu->buildPageMenu($pagesForFooter));

$staticMenu = [
    [
        'name' => 'Termine',
        'slug' => '/reparatur-termine'
    ],
    [
        'name' => 'Newsletter',
        'slug' => Configure::read('AppConfig.externNewsletterUrl'),
        'options' => [
            'target' => '_blank',
        ],
    ],
    [
        'name' => 'Widgets',
        'slug' => '/widgets/integration'
    ]

];
$menu = array_merge($menu, $staticMenu);

echo $this->Menu->render($menu, ['id' => 'footer-menu', 'class' => 'footer-menu']);
echo $this->element('core/additionalFooterHtml');
?>