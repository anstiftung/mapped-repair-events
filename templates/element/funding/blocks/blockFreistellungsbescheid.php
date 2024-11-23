<?php

use App\Model\Entity\Funding;
use Cake\Core\Configure;

echo '<fieldset>';

    echo '<legend>Freistellungsbescheid</legend>';

    /*
    echo '<div class="verification-wrapper ' . $funding->activity_proof_status_css_class . '">';
        echo '<p>' . $funding->activity_proof_status_human_readable . '</p>';
            if ($funding->activity_proof_comment != '') {
                echo '<p class="comment">' . h($funding->activity_proof_comment) . '</p>';
            }
    echo '</div>';
    */

    $formattedFundingStartDate = date('d.m.Y', strtotime(Configure::read('AppConfig.fundingsStartDate')));
    echo '<div style="margin-bottom:10px;padding:10px;">';
        echo '<p>Bitte lade hier deinen Freistellungsbescheid hoch. Dieser wird dann zeitnah von uns bestätigt.</p>';
    echo '</div>';

    echo $this->element('funding/blocks/upload/listUploadsAndUploadForm', [
        'fundinguploads' => $funding->fundinguploads_freistellungsbescheids,
        'showUploadForm' => $funding->freistellungsbecheid_status != Funding::STATUS_VERIFIED,
        'multiple' => 'multiple',
    ]);


echo '</fieldset>';
