<?php
declare(strict_types=1);

if ($object->fundingconfirmedevents_count == 0) {
    return;
}
echo '<div style="padding:5px;border-radius:3px;" class="'. $object->confirmed_events_css_class .'">';
    echo $object->fundingconfirmedevents_count . 'x';
echo '</div>';
