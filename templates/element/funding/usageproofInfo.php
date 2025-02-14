<?php
declare(strict_types=1);

use App\Model\Entity\Funding;

if ($funding->money_transfer_date === null) {
    return;
}


echo $this->element('funding/status/usageproofStatus', [
    'funding' => $funding,
    'additionalTextBefore' => 'Verwendungsnachweis: ',
]);



if ($funding->usageproof_status == Funding::STATUS_DATA_MISSING) {
    echo '<div style="margin-top:10px;margin-left:10px;">';
        echo $this->Html->link(
            'Verwendungsnachweis ' . ($funding->usageproof_exists ? 'bearbeiten' : 'erstellen'),
            $this->Html->urlFundingsUsageproof($funding->uid),
            [
                'class' => 'button',
            ],
        );
    echo '</div>';
}

if ($funding->usageproof_status == Funding::STATUS_VERIFIED_BY_ADMIN) {
    echo '<div class="download-links">';

        echo '<span style="margin-right:5px;margin-left:10px;"><i class="fa-solid fa-arrow-turn-up fa-rotate-90"></i> Downloads:</span>';

        echo $this->Html->link(
            'Verwendungsnachweis',
            'javascript:void(0);',
            [
                'target' => '_blank',
            ],
        );
    echo '</div>';
}

if ($funding->usageproof_status == Funding::STATUS_REJECTED_BY_ADMIN || $funding->usageproof_status == Funding::STATUS_PENDING) {
    echo '<div style="margin-top:10px;margin-right:5px;margin-left:10px;"><i class="fa-solid fa-arrow-turn-up fa-rotate-90"></i>';
        echo $this->Html->link(
            'Verwendungsnachweis bearbeiten',
            $this->Html->urlFundingsUsageproof($funding->uid),
            [
                'class' => 'button',
                'style' => 'margin-left:10px;',
            ],
        );
    echo '</div>';
}

