<?php
echo '<div style="padding:5px;" class="'. $object->activity_proof_status_css_class .'">';
    if ($object->workshop->funding_activity_proof_required) {
        echo $object->activity_proofs_count . 'x';
    }
echo '</div>';
