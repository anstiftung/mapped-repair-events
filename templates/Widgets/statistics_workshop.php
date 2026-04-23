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
            echo $this->Html->link($workshop->name, $this->Html->urlWorkshopDetail($workshop->url), ['target' => '_blank']);
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
    'backgroundColorRepairable' => $backgroundColorRepairable,
    'backgroundColorNotOk' => $backgroundColorNotOk,
    'backgroundColorTotal' => $backgroundColorTotal,
    'borderColorOk' => $borderColorOk,
    'borderColorRepairable' => $borderColorRepairable,
    'borderColorNotOk' => $borderColorNotOk,
    'borderColorTotal' => $borderColorTotal,
    'showCarbonFootprint' => $showCarbonFootprint,
]);

if ($showCarbonFootprint && isset($carbonFootprintSum) && $carbonFootprintSum > 0) {
    echo $this->element('widgets/carbonFootprint', ['carbonFootprintSum' => $carbonFootprintSum]);
}

if ($showBarChart && $chartHasData) {
    $this->element('addScript', ['script' =>
        JS_NAMESPACE.".WidgetStatistics.loadWorkshopDetailChartCategories('".json_encode($statisticsCategoriesData)."', '" . $backgroundColorTotal . "', '" . $borderColorTotal . "');
    "]);
?>
<canvas id="chartCategories" height="300"></canvas>
<?php } ?>

<?php
if ($showDonutChart && $chartHasData) {
    $this->element('addScript', ['script' =>
        JS_NAMESPACE.".WidgetStatistics.loadWorkshopDetailChartRepaired('".json_encode($statisticsRepairedData)."', " . (!$showBarChart ? 0 : 1) . ", '" . $backgroundColorTotal . "', '" . $borderColorTotal . "');
    "]);
?>
<canvas id="chartRepaired" height="250"></canvas>
<?php } ?>

<?php if (!$chartHasData) { ?>
    <p class="info">Für den angegeben Zeitraum sind keine Daten vorhanden.</p>
<?php } ?>