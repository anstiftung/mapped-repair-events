<?php
declare(strict_types=1);

$classes = ['verification-wrapper'];
$text = 'Freistellungsbescheid: ';
if (!empty($workshop->workshop_funding)) {
    $classes[] = $workshop->workshop_funding->freistellungsbescheid_status_css_class;
    $text .= $workshop->workshop_funding->freistellungsbescheid_status_human_readable;
    echo '<div class="' . implode(' ', $classes) . '">';
        echo $text;
    echo '</div>';
} else {
    $text .= 'notwendig';
    echo '<div>';
        echo $text;
    echo '</div>';
}

