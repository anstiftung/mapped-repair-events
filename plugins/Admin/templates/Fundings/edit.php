<?php

use App\Model\Entity\Funding;

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
    echo $this->element('heading', ['first' => 'Förderantrag (UID: ' . $funding->uid . ') bearbeiten: ' . $funding->workshop->name]);

    echo $this->Form->hidden('referer', ['value' => $referer]);
    $this->Form->unlockField('referer');

    $i = 0;
    foreach($funding->fundinguploads_activity_proofs as $fundingupload) {
        $activityProofFilenameLabel = 'Datei (' . $this->Html->link('anzeigen', $this->Html->urlFundinguploadDetail($fundingupload->id), ['target' => '_blank']) . ')';
        echo $this->Form->fieldset(
            $this->Form->control('Fundings.fundinguploads.'.$i.'.id', ['type' => 'hidden']).
            $this->Form->control('Fundings.fundinguploads.'.$i.'.filename', ['label' => $activityProofFilenameLabel, 'escape' => false, 'readonly' => true]),
            [
                'legend' => 'Aktivitätsnachweis',
            ]
        );
        $i++;
    }

    if (!empty($funding->fundinguploads_activity_proofs)) {
        echo $this->Form->control('Fundings.activity_proof_status', ['label' => 'Status', 'options' => Funding::STATUS_MAPPING]);
    }

    echo $this->element('cancelAndSaveButton', ['saveLabel' => 'Speichern']);
    echo $this->Form->end();
?>

</div>
