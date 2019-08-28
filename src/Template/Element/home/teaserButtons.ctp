<?php
$teasers = [
    'javascript:void(0);',
    'javascript:void(0);',
    '/registrierung',
    '/newsletter'
];
foreach($teasers as $link) {
    echo '<a class="teaser-button" href="' . $link . '"></a>';
}
?>