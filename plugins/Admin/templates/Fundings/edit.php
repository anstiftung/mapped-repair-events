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
    echo $this->element('heading', ['first' => 'Förderantrag (UID: ' . $funding->uid . ') ' . $funding->workshop->name]);

    echo $this->Form->hidden('referer', ['value' => $referer]);
    $this->Form->unlockField('referer');

    echo '<div class="flexbox">';

        if ($funding->workshop->funding_activity_proof_required) {
            echo $this->element('funding/fundingUploadsForm', [
                'fundinguploads' => $funding->fundinguploads_activity_proofs,
                'uploadType' => Fundingupload::TYPE_MAP[Fundingupload::TYPE_ACTIVITY_PROOF],
                'legend' => 'Uploads Aktivitätsnachweis',
            ]);

            echo '<fieldset>';
                echo '<legend>Status Aktivitätsnachweis</legend>';
                echo $this->element('funding/status/activityProofStatus', ['funding' => $funding]);
                echo $this->Form->control('Fundings.activity_proof_status', ['label' => 'Status', 'options' => Funding::STATUS_MAPPING_UPLOADS]);
                echo $this->Form->control('Fundings.activity_proof_comment', ['label' => 'Kommentar']);

            echo '</fieldset>';
        }

        echo $this->element('funding/fundingUploadsForm', [
            'fundinguploads' => $funding->fundinguploads_freistellungsbescheids,
            'uploadType' => Fundingupload::TYPE_MAP[Fundingupload::TYPE_FREISTELLUNGSBESCHEID],
            'legend' => 'Upload Freistellungsbescheid',
        ]);
        
        echo '<fieldset>';
            echo '<legend>Status Freistellungsbescheid</legend>';
            echo $this->element('funding/status/freistellungsbescheidStatus', ['funding' => $funding]);
            echo $this->Form->control('Fundings.freistellungsbescheid_status', ['label' => 'Status', 'options' => Funding::STATUS_MAPPING_UPLOADS]);
            echo $this->Form->control('Fundings.freistellungsbescheid_comment', ['label' => 'Kommentar']);
        echo '</fieldset>';

        echo $this->element('funding/blocks/blockWorkshop', ['funding' => $funding, 'disabled' => true]);
        echo $this->element('funding/blocks/blockOwnerUser', ['funding' => $funding, 'disabled' => true]);
        echo $this->element('funding/blocks/blockFundingsupporterOrganziation', ['funding' => $funding, 'disabled' => true]);
        echo $this->element('funding/blocks/blockFundingsupporterUser', ['funding' => $funding, 'disabled' => true]);
        echo $this->element('funding/blocks/blockFundingsupporterBank', ['funding' => $funding, 'disabled' => true]);
        echo $this->element('funding/blocks/blockDescription', ['funding' => $funding, 'disabled' => true]);
        echo $this->element('funding/blocks/blockCheckboxes', ['funding' => $funding, 'disabled' => true]);

        //TODO budgetplans

    echo '</div>';

    echo $this->element('cancelAndSaveButton', ['saveLabel' => 'Speichern']);
    echo $this->Form->end();
?>

</div>
