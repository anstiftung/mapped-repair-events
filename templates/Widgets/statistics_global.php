<?php
echo $this->element('widgets/statisticsFilterForm', [
    'showDonutChart' => $showDonutChart,
    'showBarChart' => $showBarChart,
    'backgroundColorOk' => $backgroundColorOk,
    'backgroundColorNotOk' => $backgroundColorNotOk,
    'borderColorOk' => $borderColorOk,
    'borderColorNotOk' => $borderColorNotOk,
    'dataSources' => $dataSources,
    'dataSource' => $dataSource,
    'month' => $month,
    'year' => $year,
    'defaultDataSource' => $defaultDataSource
]);

?>

<?php if ($carbonFootprintSum > 0) { ?>
    <?php
        $this->element('addScript', ['script' =>
            JS_NAMESPACE.".WidgetStatistics.initCarbonFootprintAnimation();
        "]);
           $infoText = '<span class="info-text">Umweltentlastung: ' . $this->Html->getCarbonFootprintAsString($carbonFootprintSum) .'</span>';
    ?>
    <div class="carbon-footprint-wrapper">
        <img src="/img/statistics/carbon-footprint.png" />
        <div class="line"></div>
        <img src="/img/statistics/carbon-footprint.png" />
        <span class="info-text"><?php echo $infoText; ?></span>
    </div>
<?php } ?>

<?php if ($materialFootprintSum > 0) { ?>
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