<?php

use App\Model\Entity\Funding;

$this->element('addScript', ['script' =>
    JS_NAMESPACE.".Helper.bindCancelButton();".
    JS_NAMESPACE.".Helper.layoutEditButtons();
"]);
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

    if (!empty($funding->fundinguploads_activity_proofs)) {
        $i = 0;
        $activityProofsFormFields = '';
        foreach($funding->fundinguploads_activity_proofs as $fundingupload) {
            $activityProofFilenameLabel = $this->Html->link('Vorschau', $this->Html->urlFundinguploadDetail($fundingupload->id), ['target' => '_blank']);
            $activityProofsFormFields .= $this->Form->control('Fundings.fundinguploads.'.$i.'.id', ['type' => 'hidden']);
            $activityProofsFormFields .= $this->Form->control('Fundings.fundinguploads.'.$i.'.filename', ['label' => $activityProofFilenameLabel, 'escape' => false, 'readonly' => true]);
            $activityProofsFormFields .= $this->Form->control('Fundings.fundinguploads.'.$i.'.created', ['label' => 'Datum', 'readonly' => true]);
            $i++;
        }
        echo $this->Form->fieldset(
            $activityProofsFormFields.
            [
                'legend' => 'Aktivitätsnachweise',
            ]
        );

    }

    if ($funding->workshop->funding_activity_proof_required) {
        echo $this->Form->fieldset(
            $this->Form->control('Fundings.activity_proof_status', ['label' => 'Status', 'options' => Funding::STATUS_MAPPING_ACTIVITY_PROOF]).
            $this->Form->control('Fundings.activity_proof_comment', ['label' => 'Kommentar']),
            [
                'legend' => 'Aktivitätsnachweis Status',
            ]
        );
    }

    echo $this->element('cancelAndSaveButton', ['saveLabel' => 'Speichern']);
    echo $this->Form->end();
?>

</div>
