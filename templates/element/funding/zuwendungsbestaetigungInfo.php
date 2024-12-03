<?php

echo $this->element('funding/status/zuwendungsbestaetigungStatus', [
    'funding' => $funding,
    'additionalTextBefore' => 'Zuwendungsbestätigung: ',
    'additionalTextAfter' => ' - ' . $this->Html->link('Jetzt hochladen', [
       'javascript:void(0)',
    ]),
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