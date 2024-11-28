<?php
$classes = [];
if ($object->is_submitted) {
    $classes[] = 'is-verified';
} else {
    $classes[] = 'is-pending';
}
echo '<div style="padding:5px;" class="'. implode(' ', $classes) .'">';
    if ($object->is_submitted) {
        echo $object->submit_date_formatted;
    } else {
        echo 'Nein';
    }
echo '</div>';
