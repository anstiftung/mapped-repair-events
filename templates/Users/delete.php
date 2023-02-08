<?php
echo $this->element('highlightNavi', [
    'main' => ''
]);
$this->element('addScript', ['script' =>
    JS_NAMESPACE.".Helper.bindCancelButton(".$loggedUser->uid.");
"]);
echo $this->element('jqueryTabsWithoutAjax', [
        'links' => $this->Html->getUserBackendNaviLinks($loggedUser->uid, true, $loggedUser->isOrga())
    ]
);
?>
<div class="profile ui-tabs custom-ui-tabs ui-widget-content">
    <div class="ui-tabs-panel">

        <?php echo $this->element('heading', ['first' => $metaTags['title'] ]); ?>

        <?php
            echo $this->Form->create(null, [
                    'novalidate' => 'novalidate',
                    'url' => $this->request->getAttribute('here'),
                    'id' => 'userDeleteForm'
                ]
            );
            echo $this->Form->hidden('referer', ['value' => $referer]);
            $this->Form->unlockField('referer');

            echo $this->Form->control('deleteMessage', ['label' => 'Ich möchte mein Profil unwiderruflich löschen, weil...', 'type' => 'textarea', 'style' => 'width:300px;height:200px;']).'<br />';

            echo $this->element('cancelAndSaveButton', ['saveLabel' => 'Profil unwiderruflich löschen']);

            echo $this->Form->end();
            ?>

    </div>


</div>
