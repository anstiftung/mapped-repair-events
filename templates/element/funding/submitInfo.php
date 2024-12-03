<?php

echo '<div class="verification-wrapper is-verified">';
    echo 'Der Förderantrag wurde am ' . $funding->submit_date_formatted . ' eingereicht und bewilligt.';
echo '</div>';

echo '<div class="download-links">';

    echo '<span style="margin-right:5px;">Downloads:</span>';

    echo $this->Html->link(
        'Förderantrag',
        $this->Html->urlFundingFoerderantragDownload($funding->uid),
        [
            'target' => '_blank',
        ],
    );
    
    echo ' / ';

    echo $this->Html->link(
        'Förderbewilligung',
        $this->Html->urlFundingFoerderbewilligungDownload($funding->uid),
        [
            'target' => '_blank',
        ],
    );

    echo ' / ';

    echo $this->Html->link(
        'Förderrichtlinie',
        '/files/foerderung/Foerderrichtlinie-anstiftung-bmuv-nov-2024.pdf',
        [
            'target' => '_blank',
        ],
    );

    echo ' / ';

    echo $this->Html->link(
        'Förderlogo BMUV',
        '/files/foerderung/Foerderlogo-BMUV.zip',
        [
            'target' => '_blank',
        ],
    );

echo '</div>';