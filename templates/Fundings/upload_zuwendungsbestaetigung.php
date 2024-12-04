<?php

$this->element('addScript', ['script' =>
    JS_NAMESPACE.".Helper.bindCancelButton();".
    JS_NAMESPACE.".Funding.initIsMissing();"
]);
echo $this->element('jqueryTabsWithoutAjax', [
    'links' => $this->Html->getUserBackendNaviLinks($loggedUser->uid, true, $loggedUser->isOrga()),
    'selected' => $this->Html->urlFundings(),
]);
?>

<div class="profile ui-tabs custom-ui-tabs ui-widget-content">
    <div class="ui-tabs-panel">
        <?php
            echo $this->element('heading', ['first' => $metaTags['title']]);
            echo $this->element('funding/blocks/blockColorCodes');

            echo $this->Form->create($funding, [
                'novalidate' => 'novalidate',
                'url' => $this->Html->urlFundingsUploadZuwendungsbestaetigung($funding->uid),
                'type' => 'file',
                'id' => 'fundingForm',
            ]);

                echo $this->Form->hidden('referer', ['value' => $referer]);
                $this->Form->unlockField('referer');

                echo '<div class="flexbox">';
                    echo $this->element('funding/blocks/blockZuwendungsbestaetigung', ['funding' => $funding]);
                echo '</div>';

                echo $this->Form->button('Zurück', [
                    'type' => 'button',
                    'id' => 'cancel-button',
                    'class' => 'rounded gray',
                ]);
    
            echo $this->Form->end();

        ?>

    </div>
</div>