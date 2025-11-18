<?php
declare(strict_types=1);

use App\Model\Entity\City;

if( empty($urlOptions) ) $urlOptions = ['url' => []];

$named = [];
if (!empty($this->request->getParam('named'))) {
    $named = $this->request->getParam('named');
}

$mergedUrlOptions = array_merge($named, ['escape' => false], $urlOptions);

$this->Paginator->options($mergedUrlOptions);

$objectNameSingular = $objectNameSingular ?? 'Datensatz';
$objectNamePlural = $objectNamePlural ?? 'Datensätze';

$fallbackNearbyUsed = $fallbackNearbyUsed ?? false;
$keyword = $keyword ?? '';
$fallbackString = $fallbackNearbyUsed ? ' im Umkreis von ' . City::FALLBACK_RADIUS_KM . ' km von "' . h($keyword) . '"' : '';

echo '<div class="pagination-search">';
    echo '<div class="numbers">';
    echo $this->Number->precision($this->Paginator->param('count'), 0);
    if (isset($allCount) && $allCount > $this->Paginator->param('count')) {
        echo ' von insgesamt ' . $this->Number->precision($allCount, 0);
        echo ' ' . ($allCount == 1 ? $objectNameSingularDativ : $objectNamePluralDativ);
        echo $fallbackString;
    } else {
        echo ' ' . ($this->Paginator->param('count') == 1 ? $objectNameSingular : $objectNamePlural);
        echo $fallbackString;
    }
    echo ' gefunden';
    echo '</div>';
echo '</div>';
?>