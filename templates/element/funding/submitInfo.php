<?php

echo '<div class="verification-wrapper is-verified">';
    echo 'Der Förderantrag wurde am ' . $funding->submit_date_formatted . ' eingereicht und bewilligt.';
echo '</div>';

echo $this->Html->link(
    'Förderantrag herunterladen',
    $this->Html->urlFundingFoerderantragDownload($funding->uid),
    [
        'target' => '_blank',
    ],
);
echo $this->Html->link(
    'Förderbewilligung herunterladen',
    $this->Html->urlFundingFoerderbewilligungDownload($funding->uid),
    [
        'target' => '_blank',
    ],
);
