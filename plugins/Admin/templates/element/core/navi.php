<?php
echo $this->Html->createMenuEntry(array(
    'name' => 'Kategorien',
    'url' => '/admin/categories/index',
    'level' => 'main'
));
echo $this->Html->createMenuEntry(array(
    'name' => 'Kenntnisse',
    'url' => '/admin/skills/index',
    'level' => 'main'
));
echo $this->Html->createMenuEntry(array(
    'name' => 'Laufzettel',
    'url' => '/admin/info-sheets/index',
    'level' => 'main'
));
echo $this->Html->createMenuEntry(array(
    'name' => 'Marken',
    'url' => '/admin/brands/index?sort=status&direction=asc',
    'level' => 'main'
));
echo $this->Html->createMenuEntry(array(
    'name' => 'Posts',
    'url' => '/admin/posts/index/?val-opt-2=1&key-opt-2=Posts.blog_id',
    'level' => 'main'
));
echo $this->Html->createMenuEntry(array(
    'name' => 'ORDS',
    'url' => '/admin/ordsCategories/index',
    'level' => 'main'
));
echo $this->Html->createMenuEntry(array(
    'name' => 'Reparatur-Initiativen',
    'url' => '/admin/workshops/index',
    'level' => 'main'
));
echo $this->Html->createMenuEntry(array(
    'name' => 'Reparaturwissen',
    'url' => '/admin/knowledges/index',
    'level' => 'main'
));
echo $this->Html->createMenuEntry(array(
    'name' => 'Seiten',
    'url' => '/admin/pages/index',
    'level' => 'main'
));
echo $this->Html->createMenuEntry(array(
    'name' => 'User',
    'url' => '/admin/users/index',
    'level' => 'main'
));
echo $this->Html->createMenuEntry(array(
    'name' => 'Termine',
    'url' => '/admin/events/index?val-status=1&key-status=Events.status',
    'level' => 'main'
));

?>