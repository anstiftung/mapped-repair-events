<?php
declare(strict_types=1);

if ($object->fundingusageproof && $object->receiptlist_difference > 0) {
    echo '<div style="padding:5px;text-align:right;border-radius:3px;" class="'. $object->receiptlist_status_css_class .'">';
        echo $this->MyNumber->formatAsDecimal($object->receiptlist_difference) . ' €';
    echo '</div>';
}
