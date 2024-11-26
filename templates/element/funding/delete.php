<?php

$this->element('addScript', ['script' =>
    JS_NAMESPACE.".Funding.bindDeleteButton(".$funding->uid.");"
]);

echo $this->Html->link(
    'Förderantrag löschen',
    'javascript:void(0);',
    [
        'class' => 'funding-delete-button',
    ],
);

