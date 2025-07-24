<?php
declare(strict_types=1);

if ($object->workshop->workshop_funding->fundingconfirmedevents_count == 0) {
    return;
}
echo '<div style="padding:5px;border-radius:3px;" class="'. $object->workshop->workshop_funding->confirmed_events_css_class .'">';
    echo $object->workshop->workshop_funding->fundingconfirmedevents_count . 'x';
echo '</div>';
