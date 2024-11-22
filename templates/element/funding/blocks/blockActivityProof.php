<?php

use App\Model\Entity\Funding;
use Cake\Core\Configure;

if (!$funding->workshop->funding_activity_proof_required) {
    return;
}

echo '<fieldset>';

    echo '<legend>Aktivitätsnachweis</legend>';

    echo '<div class="verification-wrapper ' . $funding->activity_proof_status_css_class . '">';
        echo '<p>' . $funding->activity_proof_status_human_readable . '</p>';
            if ($funding->activity_proof_comment != '' && $funding->activity_proof_status == Funding::STATUS_REJECTED) {
                echo '<p class="comment">' . h($funding->activity_proof_comment) . '</p>';
            }
    echo '</div>';

    $formattedFundingStartDate = date('d.m.Y', strtotime(Configure::read('AppConfig.fundingsStartDate')));
    echo '<div style="margin-bottom:10px;padding:10px;">';
        echo '<p>Da für die Initiative "' . h($funding->workshop->name) . '" keine Termine vor dem '.$formattedFundingStartDate.' vorhanden sind, bitten wir dich, maximal 5 Aktivitätsnachweise hochzuladen. Dieser wird dann zeitnah von uns bestätigt.</p>';
    echo '</div>';

    if (!empty($funding->fundinguploads_activity_proofs)) {
        echo 'Bereits hochgeladen<br />';
        $i = 0;
        foreach($funding->fundinguploads_activity_proofs as $fundingupload) {
            $activityProofFilenameLabel = $this->Html->link('Vorschau', $this->Html->urlFundinguploadDetail($fundingupload->id), ['target' => '_blank']);
            echo $this->Form->control('Fundings.fundinguploads.'.$i.'.id', ['type' => 'hidden']);
            echo $this->Form->control('Fundings.fundinguploads.'.$i.'.owner', ['type' => 'hidden']);
            echo $this->Form->control('Fundings.fundinguploads.'.$i.'.type', ['type' => 'hidden']);
            echo $this->Form->control('Fundings.fundinguploads.'.$i.'.filename', ['label' => $activityProofFilenameLabel, 'readonly' => true, 'class' => 'is-upload no-verify', 'escape' => false]);
            $i++;
        }
    }

    if ($funding->activity_proof_status != Funding::STATUS_VERIFIED) {

        $this->element('addScript', ['script' =>
            JS_NAMESPACE.".Funding.initBindDeleteFundinguploads();"
        ]);

        echo '<div style="margin-top:10px;padding:10px;">';
            echo '<p>Nur PDF, JPG und PNG-Dateien sind erlaubt, und jede Datei muss unter 5 MB sein.</p>';
        echo '</div>';

        echo $this->Form->control('Fundings.files_fundinguploads[]', [
            'type' => 'file',
            'multiple' => 'multiple',
            'label' => '',
            'accept' => '.jpg, .png, .pdf, .jpeg', 
        ]);

        echo '<div class="upload-button-wrapper">';
            echo $this->Form->button('Dateien hochladen', [
                'type' => 'submit',
                'class' => 'upload-button rounded',
            ]);
        echo '</div>';

    }

echo '</fieldset>';
