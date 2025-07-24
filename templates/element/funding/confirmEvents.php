<?php
declare(strict_types=1);

if ($funding->submit_date === null) {
    return;
}

echo '<div>';
    echo $this->element('funding/status/confirmedEventsStatus', [
        'funding' => $funding,
        'additionalTextBefore' => 'Veranstaltungen bestätigen: ' . $funding->fundingconfirmedevents_count . ' bestätigt',
    ]);
echo '</div>';

echo '<div style="margin-top:10px;margin-left:10px;">';
    echo $this->Html->link(
        'Veranstaltungen bestätigen',
        $this->Html->urlFundingsConfirmEvents($funding->uid),
        [
            'class' => 'button',
        ],
    );

echo '</div>';
