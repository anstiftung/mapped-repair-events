<style type="text/css">
    .truncate {
        width: <?php echo $trunc; ?>px;
    }
</style>

<?php
    $this->element('addScript', array('script' =>
        JS_NAMESPACE.".WidgetEvents.loadEventsForWorkshop(".$workshopUid.", ".$num.");"
));
?>

<div id="wrapper">
    <div id="title">Anstehende Reparaturtermine</div>
    <div id="prev">Zurück</div>
    <div id="items"><div class="loading"><img src="/img/ajax-loader.gif" alt="loading" /></div></div>
    <div id="next">Weitere Reparaturtermine</div>
</div>