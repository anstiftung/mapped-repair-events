<?php

$options = [];
if (isset($this->request->getQueryParams()['key-standard']) && isset($this->request->getQueryParams()['val-standard'])) {
    $options['url']['?']['key-standard'] = $this->request->getQueryParams()['key-standard'];
    $options['url']['?']['val-standard'] = $this->request->getQueryParams()['val-standard'];
}
if (isset($this->request->getQueryParams()['key-status']) && isset($this->request->getQueryParams()['val-status'])) {
    $options['url']['?']['key-status'] = $this->request->getQueryParams()['key-status'];
    $options['url']['?']['val-status'] = $this->request->getQueryParams()['val-status'];
}
if (isset($this->request->getQueryParams()['key-opt-1']) && isset($this->request->getQueryParams()['val-opt-1'])) {
    $options['url']['?']['key-opt-1'] = $this->request->getQueryParams()['key-opt-1'];
    $options['url']['?']['val-opt-1'] = $this->request->getQueryParams()['val-opt-1'];
}
if (isset($this->request->getQueryParams()['key-opt-2']) && isset($this->request->getQueryParams()['val-opt-2'])) {
    $options['url']['?']['key-opt-2'] = $this->request->getQueryParams()['key-opt-2'];
    $options['url']['?']['val-opt-2'] = $this->request->getQueryParams()['val-opt-2'];
}


if (isset($urlOptions)) {
    $options = array_merge($options, $urlOptions);
}

if ($this->Paginator->hasNext() || $this->Paginator->hasPrev()) {
    echo '<div class="pagination">';
        echo $this->Paginator->first(1, $options);
        echo $this->Paginator->prev(' << ', $options);
        echo $this->Paginator->numbers($options);
        echo $this->Paginator->next(' >> ', $options);
        echo $this->Paginator->last(1, $options);
    echo '</div>';
}
?>
