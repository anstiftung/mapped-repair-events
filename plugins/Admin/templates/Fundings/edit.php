<?php

use App\Model\Entity\Funding;
use App\Model\Entity\Fundingupload;

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

    echo $this->element('funding/fundingForm', [
        'fundinguploads' => $funding->fundinguploads_activity_proofs,
        'uploadType' => Fundingupload::TYPE_MAP[Fundingupload::TYPE_ACTIVITY_PROOF],
        'legend' => 'Aktivitätsnachweis',
    ]);

    if ($funding->workshop->funding_activity_proof_required) {
        echo $this->Form->fieldset(
            $this->Form->control('Fundings.activity_proof_status', ['label' => 'Status', 'options' => Funding::STATUS_MAPPING_UPLOADS]).
            $this->Form->control('Fundings.activity_proof_comment', ['label' => 'Kommentar']),
            [
                'legend' => 'Status Aktivitätsnachweis',
            ]
        );
    }

    echo $this->element('funding/fundingForm', [
        'fundinguploads' => $funding->fundinguploads_freistellungsbescheids,
        'uploadType' => Fundingupload::TYPE_MAP[Fundingupload::TYPE_FREISTELLUNGSBESCHEID],
        'legend' => 'Freistellungsbescheid',
    ]);

    echo $this->Form->fieldset(
        $this->Form->control('Fundings.freistellungsbescheid_status', ['label' => 'Status', 'options' => Funding::STATUS_MAPPING_UPLOADS]).
        $this->Form->control('Fundings.freistellungsbescheid_comment', ['label' => 'Kommentar']),
        [
            'legend' => ' Status Freistellungsbescheid',
        ]
    );

    echo $this->element('cancelAndSaveButton', ['saveLabel' => 'Speichern']);
    echo $this->Form->end();
?>

</div>
