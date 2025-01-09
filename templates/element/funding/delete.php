<?php
declare(strict_types=1);

$this->element('addScript', ['script' =>
    JS_NAMESPACE.".Funding.bindDeleteButton(".$funding->uid.");"
]);

echo $this->Html->link(
    'Förderantrag löschen',
    'javascript:void(0);',
    [
        'id' => 'funding-delete-button-' . $funding->uid,
        'class' => 'funding-delete-button',
    ],
);

