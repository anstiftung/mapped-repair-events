<?php
$this->element('addScript', array('script' =>
    JS_NAMESPACE.".Helper.bindCancelButton();"
));
echo $this->element('jqueryTabsWithoutAjax', [
    'links' => $this->Html->getUserBackendNaviLinks($loggedUser->uid, true, $loggedUser->isOrga()),
    'selected' => $this->Html->urlFunding(),
]);
?>

<div class="profile ui-tabs custom-ui-tabs ui-widget-content">
    <div class="ui-tabs-panel">
        <?php
            echo $this->element('heading', ['first' => $metaTags['title']]);

            echo $this->Form->create($workshop, [
                'novalidate' => 'novalidate',
                'url' => $this->Html->urlFundingEdit($workshop->uid),
                'id' => 'fundingEditForm'
            ]);
            echo $this->Form->hidden('referer', ['value' => $referer]);
            $this->Form->unlockField('referer');

            echo $this->Form->fieldset(
                $this->Form->control('Workshops.name', ['label' => 'Name der Initiative']).
                $this->Form->control('Workshops.street', ['label' => 'Straße + Hausnummer']).
                $this->Form->control('Workshops.zip', ['label' => 'PLZ']).
                $this->Form->control('Workshops.city', ['label' => 'Stadt']).
                $this->Form->control('Workshops.adresszusatz', ['label' => 'Adresszusatz']),
                [
                    'legend' => 'Initiative',
                ]
            );

            echo $this->element('cancelAndSaveButton', ['saveLabel' => 'Förderantrag speichern']);

            echo $this->Form->end();

        ?>

    </div>
</div>