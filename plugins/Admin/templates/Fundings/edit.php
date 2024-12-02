<?php

use App\Model\Entity\Funding;
use App\Model\Entity\Fundingupload;
use App\Model\Entity\Fundingbudgetplan;

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

    if ($funding->is_submitted) {
        echo $this->element('funding/submitInfo', ['funding' => $funding]);
        echo $this->Form->control('Fundings.reopen', ['type' => 'checkbox', 'label' => 'Förderstatus "eingereicht" zurücksetzen']);
    }

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
                echo $this->Form->control('Fundings.activity_proof_status', ['label' => 'Status', 'options' => Funding::STATUS_MAPPING_UPLOADS, 'disabled' => $funding->is_submitted]);
               
                echo $this->Form->control('Fundings.activity_proof_comment', ['label' => 'Kommentar', 'disabled' => $funding->is_submitted]);

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
            echo $this->Form->control('Fundings.freistellungsbescheid_status', ['label' => 'Status', 'options' => Funding::STATUS_MAPPING_UPLOADS, 'disabled' => $funding->is_submitted]);
            echo $this->Form->control('Fundings.freistellungsbescheid_comment', ['label' => 'Kommentar', 'disabled' => $funding->is_submitted]);
        echo '</fieldset>';

        echo $this->element('funding/blocks/blockWorkshop', ['funding' => $funding, 'disabled' => true]);
        echo $this->element('funding/blocks/blockOwnerUser', ['funding' => $funding, 'disabled' => true]);
        echo $this->element('funding/blocks/blockFundingsupporterOrganziation', ['funding' => $funding, 'disabled' => true]);
        echo $this->element('funding/blocks/blockFundingsupporterUser', ['funding' => $funding, 'disabled' => true]);
        echo $this->element('funding/blocks/blockFundingsupporterBank', ['funding' => $funding, 'disabled' => true]);
        echo $this->element('funding/blocks/blockDescription', ['funding' => $funding, 'disabled' => true]);

        echo '<fieldset>';
            echo '<legend>Kostenplan</legend>';
            foreach($funding->grouped_valid_budgetplans as $typeId => $fundingbudgetplans) {
                echo '<div class="fundingbudgetplans" style="margin-bottom:10px;">';
                    echo '<div style="margin-bottom:5px;"><b>' . Fundingbudgetplan::TYPE_MAP[$typeId] . '</b></div>';
                    foreach($fundingbudgetplans as $fundingbudgetplan) {
                        echo $fundingbudgetplan->description . ' ' . $this->MyNumber->formatAsDecimal($fundingbudgetplan->amount) . ' €<br />';
                    }
                    echo '<div style="margin-top:5px;"><i>Summe: ' . $this->MyNumber->formatAsDecimal($funding->grouped_valid_budgetplans_totals[$typeId]) . ' €</i></div>';
                echo '</div>';
            }
            echo '<div style="font-size:14px;"><b>Kosten gesamt: ' . $this->MyNumber->formatAsDecimal($funding->budgetplan_total) . ' €</b></div>';

        echo '</fieldset>';

        echo $this->element('funding/blocks/blockCheckboxes', ['funding' => $funding, 'disabled' => true]);

    echo '</div>';

    echo $this->element('cancelAndSaveButton', ['saveLabel' => 'Speichern']);
    echo $this->Form->end();
?>

</div>
