<?php
declare(strict_types=1);

if ($showWorkshopName) { ?>
    <style>
       h2 a {
           color: <?php echo $borderColorOk;?>;
       }
    </style>
    <h2>
        <?php
        echo '<a target="_blank" href="'.$this->Html->urlWorkshopDetail($workshop->url).'">'.$workshop->name.'</a>';
        ?>
    </h2>
<?php } ?>

<?php
echo $this->element('widgets/statisticsFilterForm', [
    'dateFrom' => $dateFrom,
    'dateTo' => $dateTo,
    'showWorkshopName' => $showWorkshopName,
    'showDonutChart' => $showDonutChart,
    'showBarChart' => $showBarChart,
    'backgroundColorOk' => $backgroundColorOk,
    'backgroundColorNotOk' => $backgroundColorNotOk,
    'borderColorOk' => $borderColorOk,
    'borderColorNotOk' => $borderColorNotOk,
    'showCarbonFootprint' => $showCarbonFootprint,
]);

if ($showCarbonFootprint && isset($carbonFootprintSum) && $carbonFootprintSum > 0) {
    echo $this->element('widgets/carbonFootprint', ['carbonFootprintSum' => $carbonFootprintSum]);
}

if ($showBarChart && $chartHasData) {
    $this->element('addScript', ['script' =>
        JS_NAMESPACE.".WidgetStatistics.loadWorkshopDetailChartCategories('".json_encode($statisticsCategoriesData)."');
    "]);
?>
<canvas id="chartCategories" height="300"></canvas>
<?php } ?>

<?php
if ($showDonutChart && $chartHasData) {
    $this->element('addScript', ['script' =>
        JS_NAMESPACE.".WidgetStatistics.loadWorkshopDetailChartRepaired('".json_encode($statisticsRepairedData)."', " . (!$showBarChart ? 0 : 1) . ");
    "]);
?>
<canvas id="chartRepaired" height="250"></canvas>
<?php } ?>

<?php if (!$chartHasData) { ?>
    <p class="info">Für den angegeben Zeitraum sind keine Daten vorhanden.</p>
<?php } ?>