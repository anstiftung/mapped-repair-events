<?php
$this->element('addScript', array('script' =>
    JS_NAMESPACE.".Helper.bindCancelButton();".
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

            echo $this->Form->fieldset(
                $this->Form->control('Fundings.workshop.name', ['label' => 'Name der Initiative']).
                $this->Form->control('Fundings.workshop.url', ['disabled' => true, 'label' => 'Slug']).
                $this->Form->control('Fundings.workshop.street', ['label' => 'Straße + Hausnummer']).
                $this->Form->control('Fundings.workshop.zip', ['label' => 'PLZ']).
                $this->Form->control('Fundings.workshop.city', ['label' => 'Stadt']).
                $this->Form->control('Fundings.workshop.adresszusatz', ['label' => 'Adresszusatz']).
                $this->Form->control('Fundings.workshop.country.name_de', ['disabled' => true, 'label' => 'Land']).
                $this->Form->control('Fundings.workshop.use_custom_coordinates', ['type' => 'checkbox', 'label' => 'Koordinaten selbst festlegen?']).
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
                $this->Form->control('Fundings.owner_user.country.name_de', ['disabled' => true, 'label' => 'Land']).
                $this->Form->control('Fundings.owner_user.phone', ['label' => 'Telefon']).
                $this->Form->control('Fundings.owner_user.website', ['label' => 'Website']),
                [
                    'legend' => 'Personenbezogene Daten Ansprechpartner*in (UID: ' . $funding->owner_user->uid . ')',
                ]
            );

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