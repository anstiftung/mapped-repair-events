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
        echo '<p>Für die Initiative "' . h($funding->workshop->name) . '" sind keine Termine vor dem '.$formattedFundingStartDate.' vorhanden. Bitte lade geeignete Materialien hoch, aus denen zweifelsfrei hervorgeht, dass ihr bereits mindestens eine Reparaturveranstaltung durchgeführt habt. Maximal fünf Dateien können hochgeladen werden. Wir überprüfen Nachweise zeitnah und Du wirst automatisch benachrichtigt.</p>';
    echo '</div>';

    echo $this->element('funding/blocks/upload/listUploadsAndUploadForm', [
        'uploadType' => Fundingupload::TYPE_MAP[Fundingupload::TYPE_ACTIVITY_PROOF],
        'fundinguploads' => $funding->fundinguploads_activity_proofs,
        'showUploadForm' => $funding->activity_proof_status != Funding::STATUS_VERIFIED,
        'validationMessage' => 'Nur PDF, JPG und PNG-Dateien sind erlaubt. Jede Datei muss kleiner als 5 MB sein.',
        'multiple' => 'multiple',
    ]);

echo '</fieldset>';
