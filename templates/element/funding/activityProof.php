<?php

if (!$workshop->funding_activity_proof_required) {
    return;
}
$classes = ['verification-wrapper'];
$text = 'Aktivitätsnachweis: ';
if (!empty($workshop->workshop_funding)) {
    $classes[] = $workshop->workshop_funding->activity_proof_status_css_class;
    $text .= $workshop->workshop_funding->activity_proof_status_human_readable;
    echo '<div class="' . implode(' ', $classes) . '">';
        echo $text;
    echo '</div>';
} else {
    $text .= 'notwendig';
    echo '<div>';
        echo $text;
    echo '</div>';
}

