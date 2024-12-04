<?php

use App\Model\Entity\Funding;
use App\Model\Entity\Fundingupload;

echo '<fieldset>';

    echo '<legend>Zuwendungsbestätigung</legend>';

    echo $this->element('funding/status/zuwendungsbestaetigungStatus', ['funding' => $funding, 'additionalTextBefore' => '']);

    echo '<div style="margin-bottom:10px;padding:10px;">';
        echo '<p>Die Zuwendungsbestätigung muss lesbar sein. Bitte lade alle Seiten als ein PDF (unter 5MB) hoch.</p>';
    echo '</div>';

    echo $this->element('funding/blocks/upload/listUploadsAndUploadForm', [
        'uploadType' => Fundingupload::TYPE_MAP_STEP_2[Fundingupload::TYPE_ZUWENDUNGSBESTAETIGUNG],
        'fundinguploads' => $funding->fundinguploads_zuwendungsbestaetigungs,
        'showUploadForm' => $funding->zuwendungsbestaetigung_status != Funding::STATUS_VERIFIED_BY_ADMIN,
        'validationMessage' => '',
        'multiple' => false,
    ]);

echo '</fieldset>';
