<?php

if ($object->budgetplan_total == 0) {
    return '';
}

echo '<div style="padding:5px;text-align:right;border-radius:3px;" class="'. $object->budgetplan_status_css_class .'">';
    echo $this->MyNumber->formatAsDecimal($object->budgetplan_total) . ' €';
echo '</div>';
