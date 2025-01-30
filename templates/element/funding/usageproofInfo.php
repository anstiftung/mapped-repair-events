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

if ($funding->usageproof_status != Funding::STATUS_VERIFIED_BY_ADMIN) {
    echo '<div style="margin-top:10px;margin-left:10px;">';
        echo '<span style="margin-left:5px;margin-right:5px;"><i class="fa-solid fa-arrow-turn-up fa-rotate-90"></i></span>';
        echo $this->Html->link(
            'Verwendungsnachweis ' . ($funding->usageproof_exists ? 'bearbeiten' : 'erstellen'),
            $this->Html->urlFundingsUsageproof($funding->uid),
            [
                'class' => 'button',
            ],
        );
   echo '</div>';
}