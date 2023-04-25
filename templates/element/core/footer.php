<?php
use Cake\Core\Configure;

$menu = [
    [
        'name' => '&copy; ' . date('Y'),
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
        'name' => Configure::read('AppConfig.mapWidgetEnabled') && Configure::read('AppConfig.statisticsEnabled') ? 'Widgets' : 'Widget',
        'slug' => '/widgets/integration'
    ]

];
$menu = array_merge($menu, $staticMenu);

echo $this->Menu->render($menu, ['id' => 'footer-menu', 'class' => 'footer-menu']);
echo $this->element('core/additionalFooterHtml');
?>