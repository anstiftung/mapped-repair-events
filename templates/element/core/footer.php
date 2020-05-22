<?php
use Cake\Core\Configure;

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
        'slug' => '/newsletter'
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