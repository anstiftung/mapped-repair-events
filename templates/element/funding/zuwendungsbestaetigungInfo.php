<?php

use App\Model\Entity\Funding;

echo $this->element('funding/status/zuwendungsbestaetigungStatus', [
    'funding' => $funding,
    'additionalTextBefore' => 'Zuwendungsbestätigung: ',
]);

echo '<div class="download-links">';

    echo '<span style="margin-right:5px;">Downloads:</span>';

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

echo '</div>';

echo '<div style="margin-top:10px;">';
    echo $this->Html->link(
        'Upload-Formular der Zuwendungsbestätigung',
        $this->Html->urlFundingsUploadZuwendungsbestaetigung($funding->uid),
        [
            'class' => 'button',
        ],
    );
echo '</div>';