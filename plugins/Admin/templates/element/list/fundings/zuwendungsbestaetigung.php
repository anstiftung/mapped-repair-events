<?php
declare(strict_types=1);
if (!$object->is_submitted) {
    return;
}
echo '<div style="padding:5px;border-radius:3px;" class="'. $object->zuwendungsbestaetigung_status_css_class .'">';
    echo $object->zuwendungsbestaetigungs_count . 'x';
echo '</div>';
