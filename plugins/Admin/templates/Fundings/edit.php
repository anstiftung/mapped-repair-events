<?php

$this->element('addScript', array('script' =>
    JS_NAMESPACE.".Helper.bindCancelButton();".
    JS_NAMESPACE.".Helper.layoutEditButtons();
"));
?>

<div class="admin edit">

<?php
    echo $this->Form->create($funding, [
        'novalidate' => 'novalidate',
        'id' => 'fundingForm',
    ]);
    echo $this->element('heading', ['first' => 'Förderantrag bearbeiten: ' . $funding->workshop->name . ' (UID: ' . $funding->workshop->uid . ')']); 

    echo $this->Form->hidden('referer', ['value' => $referer]);
    $this->Form->unlockField('referer');

    if ($funding->activity_proof_filename != '') {
        $activityProofFilenameLabel = 'Datei (' . $this->Html->link('anzeigen', $this->Html->urlFundingsActivityProofDetail($funding->uid), ['target' => '_blank']) . ')';
        echo $this->Form->fieldset(
            $this->Form->control('Fundings.activity_proof_filename', ['label' => $activityProofFilenameLabel, 'escape' => false]).
            $this->Form->control('Fundings.activity_proof_ok', ['label' => 'bestätigt?']),
            [
                'legend' => 'Aktivitätsnachweis',
            ]
        );
    } else {
        echo 'Kein Aktivitätsnachweis vorhanden.';
    }

?>

<?php
    echo $this->element('cancelAndSaveButton', ['saveLabel' => 'Speichern']);
    echo $this->Form->end();
?>

</div>
