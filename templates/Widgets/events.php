<?php
declare(strict_types=1);
?>
<style type="text/css">
    .truncate {
        width: <?php echo $trunc; ?>px;
    }
</style>

<?php
    $this->element('addScript', ['script' =>
        JS_NAMESPACE.".WidgetEvents.loadEventsForWorkshop(".$workshopUid.", ".$num.");"
]);
?>

<div id="wrapper">
    <div id="title">Anstehende Termine</div>
    <div id="items"><div class="loading"><img src="/img/ajax-loader.gif" alt="loading" /></div></div>
    <div id="next">Weitere Termine</div>
    <div id="prev">Zurück</div>
</div>