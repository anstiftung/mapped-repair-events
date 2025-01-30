<?php
declare(strict_types=1);

if ($object->money_transfer_date === null) {
    return;
}

echo '<div style="width:60px;">';

    echo '<div style="float:left;width:15px;padding:5px;border-radius:3px;" class="'. $object->usageproof_status_css_class .'">';
        echo '&nbsp;&nbsp;';
    echo '</div>';

    echo $this->Html->link('<i class="far fa-edit fa-border"></i>', '/admin/fundings/usageproof/' . $object->uid, [
        'escape' => false,
        'style' => 'float:left;margin-left:5px;',
    ]);

echo '</div>';