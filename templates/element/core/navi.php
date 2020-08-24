<?php

use Cake\Core\Configure;

$menu = [
    [
        'name' => 'Aktuelles',
        'slug' => $this->Html->urlBlogDetail('neuigkeiten')
    ],
    [
        'name' => 'Termine & Orte',
        'slug' => '/reparatur-termine',
        'children' => [
            [
                'name' => 'Termine',
                'slug' => $this->Html->urlEvents()
            ],
            [
                'name' => 'Orte',
                'slug' => $this->Html->urlWorkshops()
            ],
        ]
    ]
];

$menu = array_merge($menu, $this->Menu->buildPageMenu($pagesForHeader));

if (Configure::read('AppConfig.fluxBbForumEnabled')) {
    // add forum link as last child in 4th main menu item
    $forum[] = [
        'name' => 'Forum',
        'slug' => $this->Html->urlForum($appAuth->user())
    ];
    array_splice($menu[2]['children'], count($menu[2]['children']), 0, $forum);
}

if (Configure::read('AppConfig.additionalBlogCategoryEnabled') && !empty($menu[3])) {
    // add additionalBlogCategory as last child in 3rd main menu item
    $additionalBlogCategory[] = [
        'name' => Configure::read('AppConfig.additionalBlogCategoryName'),
        'slug' => $this->Html->urlBlogDetail(Configure::read('AppConfig.htmlHelper')->getAdditionalBlogCategoryUrl())
    ];
    array_splice($menu[3]['children'], count($menu[3]['children']), 0, $additionalBlogCategory);
}

// add aktive link as last child in 5th main menu item
$userMenu[] = [
    'name' => 'Aktive',
    'slug' => $this->Html->urlUsers()
];
$tagCloud[] = [
    'name' => 'Kenntnisse & Interessen',
    'slug' => $this->Html->urlSkills()
];
if (!empty($menu[5])) {
    array_splice($menu[5]['children'], count($menu[5]['children']), 0, $userMenu);
    // add tagCloud link as last child in 5th main menu item
    array_splice($menu[5]['children'], count($menu[5]['children']), 0, $tagCloud);
} else {
    // add to main menu if 5th menu item is empty
    $menu[] = $userMenu[0];
    $menu[] = $tagCloud[0];
}

echo $this->Menu->render($menu, ['id' => 'menu', 'class' => 'mainmenu']);

?>