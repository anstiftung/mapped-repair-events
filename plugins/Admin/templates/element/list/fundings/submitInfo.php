<?php
declare(strict_types=1);
$classes = [];
if ($object->is_submitted) {
    $classes[] = 'is-verified';
} else {
    if ($object->is_submittable) {
        $classes[] = 'is-pending';
    }
}
echo '<div style="padding:5px;border-radius:3px;" class="'. implode(' ', $classes) .'">';
    if ($object->is_submitted) {
        echo $object->submit_date_formatted;
    } else {
        if ($object->is_submittable) {
            echo 'Nein';
        }
    }
echo '</div>';
