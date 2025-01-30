<?php
declare(strict_types=1);

if ($object->money_transfer_date === null) {
    return;
}

echo '<div style="width:95px;">';

    echo '<div title="Status Admin" style="float:left;width:9px;padding:5px;border-radius:3px;" class="'. $object->usageproof_status_css_class .'">';
        echo 'A';
    echo '</div>';

    echo '<div title="Status Sachbericht" style="float:left;width:9px;padding:5px;border-radius:3px;margin-left:3px;" class="'. $object->usageproof_descriptions_status_css_class .'">';
        echo 'S';
    echo '</div>';

    echo '<div title="Status Belegliste" style="float:left;width:9px;padding:5px;border-radius:3px;margin-left:3px;" class="'. $object->receiptlist_status_css_class .'">';
        echo 'B';
    echo '</div>';

    echo $this->Html->link('<i class="far fa-edit fa-border"></i>', '/admin/fundings/usageproof/' . $object->uid, [
        'escape' => false,
        'style' => 'float:left;margin-left:5px;',
        'title' => 'Verwendungsnachweis bearbeiten',
    ]);

echo '</div>';