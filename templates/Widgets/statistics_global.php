<?php
declare(strict_types=1);

if ($showCityName && $city != '') { ?>
    <style>
       h2 a {
           color: <?php echo $borderColorOk;?>;
       }
    </style>
    <h2>
        <?php
            echo $this->Html->link(h($city), $this->Html->urlEvents($city), ['target' => '_blank']);
        ?>
    </h2>
<?php } ?>

<?php
echo $this->element('widgets/statisticsFilterForm', [
    'showDonutChart' => $showDonutChart,
    'showBarChart' => $showBarChart,
    'backgroundColorOk' => $backgroundColorOk,
    'backgroundColorNotOk' => $backgroundColorNotOk,
    'borderColorOk' => $borderColorOk,
    'borderColorNotOk' => $borderColorNotOk,
    'dataSources' => $city == '' ? $dataSources : [],
    'dataSource' => $dataSource,
    'month' => $month,
    'year' => $year,
    'defaultDataSource' => $defaultDataSource,
    'city' => $city,
    'showCityName' => $showCityName,
]);

if ($carbonFootprintSum > 0) {
    echo $this->element('widgets/carbonFootprint', ['carbonFootprintSum' => $carbonFootprintSum]);
}

if ($materialFootprintSum > 0) { ?>
    <?php
        $this->element('addScript', ['script' =>
            JS_NAMESPACE.".WidgetStatistics.initMaterialFootprintAnimation();
        "]);
    ?>
    <div class="material-footprint-wrapper">
        <span class="img-wrapper">
            <img src="/img/statistics/material-footprint-2.png" />
            <b></b>
        </span><br />
        <span class="info-text"><?php echo $this->Html->getMaterialFootprintAsString($materialFootprintSum); ?></span>
    </div>
<?php } ?>

<?php if (isset($workshopCount) && $workshopCount > 0) { ?>
    <div class="workshop-count-wrapper">
        <span class="info-text"><?php echo $this->Html->getWorkshopsCountForGlobalStatisticsString($workshopCount); ?></span>
    </div>
<?php } ?>

<?php
if ($showBarChart && $chartHasData) {
    $this->element('addScript', ['script' =>
        JS_NAMESPACE.".WidgetStatistics.loadWorkshopDetailChartCategories('".json_encode($statisticsCategoriesData)."');
    "]);
?>
<canvas id="chartCategories" height="300"></canvas>
<?php } ?>

<?php
if ($dataSource == 'platform' && $showDonutChart && $chartHasData) {
    $this->element('addScript', ['script' =>
        JS_NAMESPACE.".WidgetStatistics.loadWorkshopDetailChartRepaired('".json_encode($statisticsRepairedData)."');
    "]);
?>
<canvas id="chartRepaired" width="75%"></canvas>
<?php } ?>

<?php if (!$chartHasData) { ?>
    <p class="info">Für den angegeben Zeitraum sind keine Daten vorhanden.</p>
<?php } ?>