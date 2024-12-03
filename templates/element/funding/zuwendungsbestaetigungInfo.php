<?php

use App\Model\Entity\Funding;

echo $this->element('funding/status/zuwendungsbestaetigungStatus', [
    'funding' => $funding,
    'additionalTextBefore' => 'Zuwendungsbestätigung: ',
]);

echo '<div class="download-links">';

    echo '<span style="margin-right:5px;">Downloads:</span>';

    echo $this->Html->link(
        'Formular-Vorlage',
        'https://anstiftung.de/images/vorlage_zuwendungsbestaetigung_geldzuwendung.pdf',
        [
            'target' => '_blank',
        ],
    );
    
    echo ' / ';

    echo $this->Html->link(
        'Ausfüllhilfe',
        'https://anstiftung.de/images/vorlage_zuwendungsbestaetigung_geldzuwendung_ausfuellhilfe.pdf',
        [
            'target' => '_blank',
        ],
    );    

echo '</div>';

if ($funding->zuwendungsbestaetigung_status == Funding::STATUS_VERIFIED_BY_ADMIN) {
    return;
}

echo '<div style="margin-top:10px;">';
    echo $this->Html->link(
        'Zuwendungsbestätigung hochladen',
        'javascript:void(0);',
        [
            'class' => 'button',
        ],
    );
echo '</div>';