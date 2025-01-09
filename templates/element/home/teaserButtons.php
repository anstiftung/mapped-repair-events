<?php
declare(strict_types=1);
$teasers = [
    ['url' => 'javascript:void(0);'],
    ['url' => 'javascript:void(0);'],
    ['url' => '/registrierung'],
    ['url' => 'javascript:void(0);', 'target' => '_blank'],
];
foreach($teasers as $teaser) {
    echo '<a class="teaser-button"
        href="' . $teaser['url'] . '"' .
        (isset($teaser['target']) ? ' target="' . $teaser['target'] . '"' : '') . '></a>';
}
?>