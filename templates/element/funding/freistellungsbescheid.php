<?php

$classes = [];
$text = 'Freistellungsbescheid: ';
if (!empty($workshop->workshop_funding)) {
    $classes[] = $workshop->workshop_funding->freistellungsbescheid_status_css_class;
    $text .= $workshop->workshop_funding->freistellungsbescheid_status_human_readable;
    echo '<div style="padding:10px;margin-top:10px;border-radius:3px;" class="' . implode(' ', $classes) . '">';
        echo $text;
    echo '</div>';
} else {
    $text .= 'notwendig';
    echo '<div>';
        echo $text;
    echo '</div>';
}

