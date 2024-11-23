<?php

$this->element('addScript', ['script' =>
    JS_NAMESPACE.".Helper.bindCancelButton();".
    JS_NAMESPACE.".Funding.bindDeleteButton(".$funding->uid.");".
    JS_NAMESPACE.".Funding.initIsMissing();".
    JS_NAMESPACE.".Funding.initTextareaCounter();".
    JS_NAMESPACE.".Funding.initIsVerified('".json_encode($funding->verified_fields)."');".
    JS_NAMESPACE.".Funding.updateProgressBar(" . $funding->verified_fields_count . ",  ".$funding->required_fields_count.");"
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
                'url' => $this->Html->urlFundingsEdit($funding->workshop->uid),
                'type' => 'file',
                'id' => 'fundingForm',
            ]);

                echo $this->Form->hidden('referer', ['value' => $referer]);
                $this->Form->unlockField('referer');
                $this->Form->unlockField('verified_fields');
                echo $this->Form->hidden('Fundings.workshop.use_custom_coordinates');
                echo $this->Form->hidden('Fundings.owner_user.use_custom_coordinates');

                echo $this->element('funding/blocks/blockProgressBar', ['funding' => $funding]);

                echo '<div class="flexbox">';
                    echo $this->element('funding/blocks/blockActivityProof', ['funding' => $funding]);
                    echo $this->element('funding/blocks/blockWorkshop', ['funding' => $funding]);
                    echo $this->element('funding/blocks/blockOwnerUser', ['funding' => $funding]);
                    echo $this->element('funding/blocks/blockFundingsupporterOrganziation', ['funding' => $funding]);
                    echo $this->element('funding/blocks/blockFundingsupporterUser', ['funding' => $funding]);
                    echo $this->element('funding/blocks/blockFundingsupporterBank', ['funding' => $funding]);
                    echo $this->element('funding/blocks/blockDescription', ['funding' => $funding]);
                    echo $this->element('funding/blocks/blockBudgetplan', ['funding' => $funding]);
                echo '</div>';

                echo $this->element('funding/blocks/blockProgressBar', ['funding' => $funding]);

                $deleteButton = $this->Form->button('Förderantrag löschen', [
                    'type' => 'button',
                    'id' => 'delete-button',
                    'class' => 'rounded red',
                ]);

                echo $this->element('cancelAndSaveButton', [
                    'saveLabel' => 'Förderantrag zwischenspeichern',
                    'additionalButton' => $deleteButton,
                ]);

            echo $this->Form->end();

        ?>

    </div>
</div>