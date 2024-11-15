<?php
echo '<div style="padding:5px;" class="'. ($object->all_fields_verified ? 'is-verified' : 'is-pending') .'">';
    echo $object->verified_fields_count . ' / ' . $object->required_fields_count;
echo '</div>';
