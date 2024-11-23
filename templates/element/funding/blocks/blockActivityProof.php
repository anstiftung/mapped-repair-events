<?php

use App\Model\Entity\Funding;
use App\Model\Entity\Fundingupload;
use Cake\Core\Configure;

if (!$funding->workshop->funding_activity_proof_required) {
    return;
}

echo '<fieldset>';

    echo '<legend>Aktivitätsnachweis</legend>';

    echo '<div class="verification-wrapper ' . $funding->activity_proof_status_css_class . '">';
        echo '<p>' . $funding->activity_proof_status_human_readable . '</p>';
            if ($funding->activity_proof_comment != '') {
                echo '<p class="comment">' . h($funding->activity_proof_comment) . '</p>';
            }
    echo '</div>';

    $formattedFundingStartDate = date('d.m.Y', strtotime(Configure::read('AppConfig.fundingsStartDate')));
    echo '<div style="margin-bottom:10px;padding:10px;">';
        echo '<p>Da für die Initiative "' . h($funding->workshop->name) . '" keine Termine vor dem '.$formattedFundingStartDate.' vorhanden sind, bitten wir dich, maximal 5 Aktivitätsnachweise hochzuladen. Dieser wird dann zeitnah von uns bestätigt.</p>';
    echo '</div>';

    echo $this->element('funding/blocks/upload/listUploadsAndUploadForm', [
        'uploadType' => Fundingupload::TYPE_ACTIVITY_PROOF,
        'fundinguploads' => $funding->fundinguploads_activity_proofs,
        'showUploadForm' => $funding->activity_proof_status != Funding::STATUS_VERIFIED,
        'multiple' => 'multiple',
    ]);

echo '</fieldset>';
