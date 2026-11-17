<?php
declare(strict_types=1);

/** @var array<int, string> $seriesDescriptions */

if (!empty($object->series_uid)) {
    $seriesHint = 'Dieser Termin ist Teil einer Terminserie';
    if (isset($seriesDescriptions[$object->series_uid])) {
        $seriesHint .= ': ' . $seriesDescriptions[$object->series_uid];
    }
    echo '<i class="fa fa-repeat event-series-indicator" title="' . h($seriesHint) . '" aria-label="' . h($seriesHint) . '"></i>';
}