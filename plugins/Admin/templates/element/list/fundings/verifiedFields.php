<?php
declare(strict_types=1);
$class = $object->user_fields_verified ? 'is-verified' : 'is-pending';

if ($object->user_fields_verified_count == 0) {
    $class = 'is-missing';
}

echo '<div style="padding:5px;text-align:right;border-radius:3px;" class="'. $class .'">';
    echo $object->user_fields_verified_count . ' / ' . $object->user_fields_count;
echo '</div>';
