<?php
$this->element('addScript', array('script' =>
    JS_NAMESPACE.".Helper.bindCancelButton();"
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
                'url' => $this->Html->urlFundingsUploadActivityProof($funding->workshop_uid),
                'enctype' => 'multipart/form-data',
                'id' => 'fundingForm'
            ]);
            echo $this->Form->hidden('referer', ['value' => $referer]);
            $this->Form->unlockField('referer');

            echo '<div class="upload-form-field-wrapper">';
                echo $this->Form->control('Fundings.activity_proof', ['type' => 'file', 'label' => 'Aktivitätsnachweis auswählen']);
                echo '<span class="hint">PDF, JPG oder PNG-Dateien  / max. 5 MB.</span>';
            echo '</div>';

            echo $this->element('cancelAndSaveButton', ['saveLabel' => 'Aktivitätsnachweis hochladen']);

            echo $this->Form->end();

        ?>

    </div>
</div>