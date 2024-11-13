<?php

use App\Model\Entity\Funding;
$this->element('addScript', array('script' =>
    JS_NAMESPACE.".Helper.bindCancelButton();".
    JS_NAMESPACE.".Funding.bindDeleteButton(".$funding->uid.");".
    JS_NAMESPACE.".Funding.init();".
    JS_NAMESPACE.".Funding.addIsVerifiedCheckboxToFundingEdit('".json_encode($funding->verified_fields)."');"
));
echo $this->element('jqueryTabsWithoutAjax', [
    'links' => $this->Html->getUserBackendNaviLinks($loggedUser->uid, true, $loggedUser->isOrga()),
    'selected' => $this->Html->urlFundings(),
]);
?>

<div class="profile ui-tabs custom-ui-tabs ui-widget-content">
    <div class="ui-tabs-panel">
        <?php
            echo $this->element('heading', ['first' => $metaTags['title']]);

            echo $this->Form->create($funding, [
                'novalidate' => 'novalidate',
                'url' => $this->Html->urlFundingsEdit($funding->workshop->uid),
                'id' => 'fundingForm'
            ]);

            ?>

            <div class="color-codes-wrapper">
                <div class="is-verified">
                    bestätigt
                </div>
                <div class="is-pending">
                    Bestätigung ausstehend
                </div>
                <div class="is-missing">
                    fehlt
                </div>
                <div class="is-rejected">
                    durch Admin beanstandet
                </div>
            </div>
    
        <?php

            echo $this->Form->hidden('referer', ['value' => $referer]);
            $this->Form->unlockField('referer');
            $this->Form->unlockField('verified_fields');

            echo '<div class="flexbox">';
                echo $this->Form->fieldset(
                    Funding::getRenderedFields(Funding::FIELDS_WORKSHOP, 'workshop', $this->Form),
                    [
                        'legend' => 'Stammdaten der Reparatur-Initiative (UID: ' . $funding->workshop->uid . ')'
                    ]
                );

                echo $this->Form->fieldset(
                    Funding::getRenderedFields(Funding::FIELDS_OWNER_USER, 'owner_user', $this->Form),
                    [
                        'legend' => 'Personenbezogene Daten Ansprechpartner*in (UID: ' . $funding->owner_user->uid . ')',
                    ]
                );

                echo $this->Form->fieldset(
                    Funding::getRenderedFields(Funding::FIELDS_SUPPORTER_ORGANIZATION, 'owner_user', $this->Form),
                    [
                        'legend' => 'Personenbezogene Daten Ansprechpartner*in (UID: ' . $funding->owner_user->uid . ')',
                    ]
                );

                echo $this->Form->fieldset(
                    Funding::getRenderedFields(Funding::FIELDS_SUPPORTER_ORGANIZATION, 'supporter', $this->Form),
                    [
                        'legend' => 'Stammdaten der Trägerorganisation',
                    ]
                );

                echo $this->Form->fieldset(
                    Funding::getRenderedFields(Funding::FIELDS_SUPPORTER_USER, 'supporter', $this->Form),
                    [
                        'legend' => 'Ansprechpartner*in der Trägerorganisation',
                    ]
                );

                echo $this->Form->fieldset(
                    $this->Form->control('Fundings.supporter.bank_account_owner', ['label' => 'Kontoinhaber']).
                    $this->Form->control('Fundings.supporter.bank_institute', ['label' => 'Kreditinstitut']).
                    $this->Form->control('Fundings.supporter.iban', ['label' => 'IBAN']),
                    [
                        'legend' => 'Bankverbindung der Trägerorganisation',
                    ]
                );

                echo '</div>';

                $deleteButton = $this->Form->button('Förderantrag löschen', [
                    'type' => 'button',
                    'id' => 'delete-button',
                    'class' => 'rounded red',
                ]);
    
            echo $this->element('cancelAndSaveButton', [
                'saveLabel' => 'Förderantrag speichern',
                'additionalButton' => $deleteButton,
            ]);

            echo $this->Form->end();

        ?>

    </div>
</div>