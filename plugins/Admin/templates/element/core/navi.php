<?php
declare(strict_types=1);

use Cake\Core\Configure;
if (Configure::read('isApiEnabled')) {
    echo $this->Html->createMenuEntry([
        'name' => 'API-Token',
        'url' => '/admin/apiTokens/index',
        'level' => 'main',
    ]);
}
echo $this->Html->createMenuEntry([
    'name' => 'Abos',
    'url' => '/admin/worknews/index',
    'level' => 'main',
]);
echo $this->Html->createMenuEntry([
    'name' => 'E-Mails',
    'url' => '/admin/queuedJobs',
    'level' => 'main',
]);
echo $this->Html->createMenuEntry([
    'name' => 'Kategorien',
    'url' => '/admin/categories/index',
    'level' => 'main',
]);
echo $this->Html->createMenuEntry([
    'name' => 'Kenntnisse',
    'url' => '/admin/skills/index',
    'level' => 'main',
]);
if (Configure::read('AppConfig.statisticsEnabled')) {
    echo $this->Html->createMenuEntry([
        'name' => 'Laufzettel',
        'url' => '/admin/info-sheets/index',
        'level' => 'main',
    ]);
    echo $this->Html->createMenuEntry([
        'name' => 'Marken',
        'url' => '/admin/brands/index?sort=status&direction=asc',
        'level' => 'main',
    ]);
}
echo $this->Html->createMenuEntry([
    'name' => 'Posts',
    'url' => '/admin/posts/index/?val-opt-2=1&key-opt-2=Posts.blog_id',
    'level' => 'main',
]);
if (Configure::read('AppConfig.statisticsEnabled')) {
    echo $this->Html->createMenuEntry([
        'name' => 'ORDS',
        'url' => '/admin/ordsCategories/index',
        'level' => 'main',
    ]);
}
echo $this->Html->createMenuEntry([
    'name' => 'Initiativen',
    'url' => '/admin/workshops/index',
    'level' => 'main',
]);
echo $this->Html->createMenuEntry([
    'name' => 'Reparatur-wissen',
    'url' => '/admin/knowledges/index',
    'level' => 'main',
]);
echo $this->Html->createMenuEntry([
    'name' => 'Seiten',
    'url' => '/admin/pages/index',
    'level' => 'main',
]);
echo $this->Html->createMenuEntry([
    'name' => 'User',
    'url' => '/admin/users/index',
    'level' => 'main',
]);
echo $this->Html->createMenuEntry([
    'name' => 'Termine',
    'url' => '/admin/events/index?val-status=1&key-status=Events.status',
    'level' => 'main',
]);
if (Configure::read('AppConfig.fundingsEnabled')) {
    echo $this->Html->createMenuEntry([
        'name' => 'Förder-anträge',
        'url' => '/admin/fundings/index',
        'level' => 'main',
    ]);
}
?>