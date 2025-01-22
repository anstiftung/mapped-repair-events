<?php
declare(strict_types=1);
    $this->element('addScript', ['script' =>
        JS_NAMESPACE.".WidgetStatistics.initCarbonFootprintAnimation();
    "]);
    $infoText = '<span class="info-text"><a href="'.$this->Html->urlPageDetail('statistik').'" target="_blank">Umweltentlastung</a></b>: ' . $this->Html->getCarbonFootprintAsString($carbonFootprintSum) .'</span>';
?>

<div class="carbon-footprint-wrapper">
    <img src="/img/statistics/carbon-footprint.png" />
    <div class="line"></div>
    <img src="/img/statistics/carbon-footprint.png" />
    <span class="info-text"><?php echo $infoText; ?></span>
</div>