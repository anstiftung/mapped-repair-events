<?php
declare(strict_types=1);

    $this->element('addScript', ['script' =>
        JS_NAMESPACE.".WidgetStatistics.initFilterForm();
    "]);

    echo $this->Form->create(null, [
        'novalidate' => 'novalidate',
        'type' => 'get',
        'url' => $this->request->getAttribute('here')
    ]);
        if (isset($showDonutChart)) {
            echo $this->Form->hidden('showDonutChart', ['value' => $showDonutChart]);
        }
        if (isset($showBarChart)) {
            echo $this->Form->hidden('showBarChart', ['value' => $showBarChart]);
        }
        if (isset($showWorkshopName)) {
            echo $this->Form->hidden('showWorkshopName', ['value' => $showWorkshopName]);
        }
        if (isset($showCarbonFootprint)) {
            echo $this->Form->hidden('showCarbonFootprint', ['value' => $showCarbonFootprint]);
        }
        if (isset($city)) {
            echo $this->Form->hidden('city', ['value' => $city]);
        }
        if (isset($showCityName)) {
            echo $this->Form->hidden('showCityName', ['value' => $showCityName]);
        }
        echo $this->Form->hidden('backgroundColorOk', ['value' => $backgroundColorOk]);
        echo $this->Form->hidden('backgroundColorRepairable', ['value' => $backgroundColorRepairable]);
        echo $this->Form->hidden('backgroundColorNotOk', ['value' => $backgroundColorNotOk]);
        echo $this->Form->hidden('borderColorOk', ['value' => $borderColorOk]);
        echo $this->Form->hidden('borderColorRepairable', ['value' => $borderColorRepairable]);
        echo $this->Form->hidden('borderColorNotOk', ['value' => $borderColorNotOk]);
        echo $this->Form->hidden('today', ['value' => date('d.m.Y')]);
        if (isset($defaultDataSource)) {
            echo $this->Form->hidden('defaultDataSource', ['value' => $defaultDataSource]);
        }

        if (isset($year)) {
            echo '<div class="input select">';
            echo $this->Form->year('year', ['val' => $year, 'empty' => 'Jahr: alle', 'min' => 2010, 'max' => date('Y')]);
            echo '</div>';
        }
        if (isset($month)) {
            echo $this->Form->control('month', ['type' => 'select', 'label' => '', 'val' => $month, 'options' => $this->Time->getMonths(), 'empty' => 'Monat: alle']);
        }

        if (isset($dateFrom) && isset($dateTo)) {
            echo $this->element('datepicker');
            if (!$this->request->getSession()->read('isMobile')) {
                echo '<span>Zeitraum: </span>';
            }
            echo $this->Form->control('dateFrom',  ['type' => 'text', 'label' => '', 'value' => $dateFrom, 'class' => 'datepicker-input']);
            echo '<span>bis</span>';
            echo $this->Form->control('dateTo',  ['type' => 'text', 'label' => '', 'value' => $dateTo, 'class' => 'datepicker-input']);
        }
        if (!empty($dataSources) && $city == '') {
            echo $this->Form->control('dataSource',  ['type' => 'select', 'label' => '', 'options' => $dataSources, 'value' => $dataSource]);
        }
        echo '<button id="reset" class="button gray">Zurücksetzen</button>';
    echo $this->Form->end();

?>