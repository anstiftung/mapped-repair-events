<?php
echo '<div style="padding:5px;" class="'. $object->activity_proof_status_css_class .'">';
    echo $object->workshop->funding_activity_proof_required ? 'Ja' : 'Nein';
    if ($object->workshop->funding_activity_proof_required) {
        echo ' / Uploads: ' . $object->activity_proofs_count;
    }
echo '</div>';