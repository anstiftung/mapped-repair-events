<?php
declare(strict_types=1);

use Cake\Core\Configure;
use Cake\I18n\DateTime;

$fundingFinished = $this->Time->isFundingFinished();
$classes = [
    'verification-wrapper',
    $fundingFinished ? 'is-missing' : 'is-verified',
];

echo '<div class="' . implode(' ', $classes) . '" style="margin-top:10px;">';
    if ($fundingFinished) {
        echo '<p>Die Antragstellung ist nicht mehr möglich.</p>';
    } else {
        $endDate = DateTime::createFromFormat('Y-m-d H:i:s', Configure::read('AppConfig.fundingsEndDateNTime'))->i18nFormat(Configure::read('DateFormat.de.DateLongNTimeWithWeekday'));
        echo 'Die Antragstellung ist bis <b>' . $endDate  .' Uhr</b> möglich.';
    }
echo '</div>';
