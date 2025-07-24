<?php
declare(strict_types=1);

if ($funding->submit_date === null) {
    return;
}

echo '<div style="margin-top:10px;margin-left:10px;">';
    echo $this->Html->link(
        'Veranstaltungen bestätigen',
        $this->Html->urlFundingsConfirmEvents($funding->uid),
        [
            'class' => 'button',
        ],
    );
    $confirmedCount = $workshop->fundingconfirmedevents[0]->count;
    if ($confirmedCount > 0) {
        echo '<div style="margin-top:10px;">';
            echo 'Bestätigte Veranstaltungen: ' . $confirmedCount;
        echo '</div>';
    }
echo '</div>';
