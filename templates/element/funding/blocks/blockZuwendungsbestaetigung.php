<?php
declare(strict_types=1);

use App\Model\Entity\Funding;
use App\Model\Entity\Fundingupload;

echo '<fieldset>';

    echo '<legend>Zuwendungsbestätigung</legend>';

    echo $this->element('funding/status/zuwendungsbestaetigungStatus', ['funding' => $funding, 'additionalTextBefore' => '']);

    echo '<div style="margin-bottom:10px;padding:10px;">';
        
        echo '<p style="margin-bottom:10px;">';
            echo 'Bitte die Ausfüllhilfe beachten. Das Formular muss korrekt ausgefüllt sein. Bitte lade alle Seiten als ein PDF (unter 5MB) fristgerecht hoch.';
        echo '</p>';

        echo '<p>';
            echo $this->Html->link(
                'Formular-Vorlage Zuwendungsbestätigung',
                'https://anstiftung.de/images/vorlage_zuwendungsbestaetigung_geldzuwendung.pdf',
                [
                    'target' => '_blank',
                ],
            );
            
            echo ' / ';
        
            echo $this->Html->link(
                'Ausfüllhilfe Zuwendungsbestätigung',
                'https://anstiftung.de/images/vorlage_zuwendungsbestaetigung_geldzuwendung_ausfuellhilfe.pdf',
                [
                    'target' => '_blank',
                ],
            );
        echo '</p>';
    echo '</div>';

    echo $this->element('funding/blocks/upload/listUploadsAndUploadForm', [
        'uploadType' => Fundingupload::TYPE_MAP_STEP_2[Fundingupload::TYPE_ZUWENDUNGSBESTAETIGUNG],
        'fundinguploads' => $funding->fundinguploads_zuwendungsbestaetigungs,
        'showUploadForm' => $funding->zuwendungsbestaetigung_status != Funding::STATUS_VERIFIED_BY_ADMIN,
        'validationMessage' => '',
        'multiple' => false,
    ]);

echo '</fieldset>';
