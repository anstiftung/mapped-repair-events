<?php
declare(strict_types=1);

if (!$this->request->getSession()->read('isMobile')) {
    echo $this->element('addScript', ['script' =>
        JS_NAMESPACE.".Helper.fixChartInHiddenIframe();
    "]);
}
?>

<div id="tabs-3" class="<?php echo !$this->request->getSession()->read('isMobile') ? 'force-show' : '';?>">
    <div class="widget-link statistics">
        <a title="Statistik auf deiner Webseite?" href="/widgets/integration/#3">Statistik auf deiner Webseite?</a>
    </div>
    <?php
       $heightAttribute = 'data-height';
       $height = 1250;
       if ($this->request->getSession()->read('isMobile')) {
           $heightAttribute = 'height';
           $height = 700;
           echo '<h2>Statistik</h2>';
       }
    ?>
    <iframe src="/widgets/statistics-workshop/<?php echo $workshop->uid; ?>?showWorkshopName=false&showCarbonFootprint=<?php echo $showCarbonFootprint; ?>" scrolling="no" border="0" width="100%" <?php echo $heightAttribute; ?>="<?php echo $height; ?>" style="overflow:hidden;margin-bottom:10px;"></iframe>
</div>