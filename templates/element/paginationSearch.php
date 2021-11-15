<?php

if( empty($urlOptions) ) $urlOptions = ['url' => []];

$named = [];
if (!empty($this->request->getParam('named'))) {
    $named = $this->request->getParam('named');
}

$mergedUrlOptions = array_merge($named, ['escape' => false], $urlOptions);

$this->Paginator->options($mergedUrlOptions);

if (!isset($objectNameSingular)) { $objectNameSingular = 'Datensatz'; };
if (!isset($objectNamePlural)) { $objectNamePlural = 'Datensätze'; };

echo '<div class="pagination-search">';
    echo '<div class="numbers">';
    echo $this->Number->precision($this->Paginator->param('count'), 0);
    if (isset($allCount) && $allCount > $this->Paginator->param('count')) {
        echo ' von insgesamt ' . $this->Number->precision($allCount, 0);
        echo ' ' . ($allCount == 1 ? $objectNameSingularDativ : $objectNamePluralDativ);
    } else {
        echo ' ' . ($this->Paginator->param('count') == 1 ? $objectNameSingular : $objectNamePlural);
    }
    echo ' gefunden';
    echo '</div>';
echo '</div>';
?>