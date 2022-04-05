<?php
    $this->element('addScript', ['script' =>
        JS_NAMESPACE.".Helper.initBoxToggle();
    "]);
?>

<p><b>Nutze die nachfolgenden Widgets, um deine eigenen Veranstaltungen oder Initiativen in deiner Nähe auf deiner eigenen Webseite darzustellen.</b></p>
<p>&nbsp;</p>

<div id="wswidget">
    <?php echo $this->element('widgetDocs/event'); ?>
    <?php echo $this->element('widgetDocs/map'); ?>
    <?php echo $this->element('widgetDocs/statistic'); ?>
</div>
