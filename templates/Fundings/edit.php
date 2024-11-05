<?php
$this->element('addScript', array('script' =>
    JS_NAMESPACE.".Helper.bindCancelButton();".
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
            echo $this->Form->hidden('referer', ['value' => $referer]);
            $this->Form->unlockField('referer');
            $this->Form->unlockField('verified_fields');

            echo '<div class="flexbox">';

                echo $this->Form->fieldset(
                    $this->Form->control('Fundings.workshop.name', ['label' => 'Name der Initiative']).
                    $this->Form->control('Fundings.workshop.street', ['label' => 'Straße + Hausnummer']).
                    $this->Form->control('Fundings.workshop.zip', ['label' => 'PLZ']).
                    $this->Form->control('Fundings.workshop.city', ['label' => 'Stadt']).
                    $this->Form->control('Fundings.workshop.adresszusatz', ['label' => 'Adresszusatz']).
                    $this->Form->control('Fundings.workshop.email', ['label' => 'E-Mail']).
                    $this->Form->control('Fundings.workshop.website', ['label' => 'Website']),
                    [
                        'legend' => 'Stammdaten der Reparatur-Initiative (UID: ' . $funding->workshop->uid . ')'
                    ]
                );

                echo $this->Form->fieldset(
                    $this->Form->control('Fundings.owner_user.nick', ['label' => 'Nickname']).
                    $this->Form->control('Fundings.owner_user.firstname', ['label' => 'Vorname']).
                    $this->Form->control('Fundings.owner_user.lastname', ['label' => 'Nachname']).
                    $this->Form->control('Fundings.owner_user.email', ['label' => 'E-Mail']).
                    $this->Form->control('Fundings.owner_user.street', ['type' => 'textarea', 'label' => 'Anschrift']).
                    $this->Form->control('Fundings.owner_user.zip', ['label' => 'PLZ']).
                    $this->Form->control('Fundings.owner_user.city', ['label' => 'Stadt']).
                    $this->Form->control('Fundings.owner_user.phone', ['label' => 'Telefon']).
                    $this->Form->control('Fundings.owner_user.website', ['label' => 'Website']),
                    [
                        'legend' => 'Personenbezogene Daten Ansprechpartner*in (UID: ' . $funding->owner_user->uid . ')',
                    ]
                );

                echo $this->Form->fieldset(
                    $this->Form->control('Fundings.supporter.name', ['label' => 'Name']).
                    $this->Form->control('Fundings.supporter.legal_form', ['label' => 'Rechtsform']).
                    $this->Form->control('Fundings.supporter.website', ['label' => 'Website']).
                    $this->Form->control('Fundings.supporter.street', ['type' => 'textarea', 'label' => 'Anschrift']).
                    $this->Form->control('Fundings.supporter.zip', ['label' => 'PLZ']).
                    $this->Form->control('Fundings.supporter.city', ['label' => 'Stadt']),
                    [
                        'legend' => 'Stammdaten der Trägerorganisation',
                    ]
                );

                echo $this->Form->fieldset(
                    $this->Form->control('Fundings.supporter.contact_firstname', ['label' => 'Vorname']).
                    $this->Form->control('Fundings.supporter.contact_lastname', ['label' => 'Nachname']).
                    $this->Form->control('Fundings.supporter.contact_function', ['label' => 'Funktion']).
                    $this->Form->control('Fundings.supporter.contact_phone', ['label' => 'Telefon']).
                    $this->Form->control('Fundings.supporter.contact_email', ['label' => 'E-Mail']),
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

            echo $this->element('cancelAndSaveButton', ['saveLabel' => 'Förderantrag speichern']);

            echo $this->Form->end();

        ?>

    </div>
</div>