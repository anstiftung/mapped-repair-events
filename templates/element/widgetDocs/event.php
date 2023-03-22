<?php
use Cake\Core\Configure;
?>

<div id="1" href="#collapse1" class="box-toggle box-toggle1">
    <?php echo __('Widgetpage: Eventsbox Integration'); ?>
</div>
<div id="collapse1" class="collapse">
    Kopiere folgenden Code, um den nächsten Termin deiner <?php echo Configure::read('AppConfig.initiativeNameSingular'); ?> auf deiner eigenen Webseite anzuzeigen
    <br />

    <strong class="highlight">Beispiel-Link:</strong> <a title="Voransicht" target="_blank" href="/widgets/test-widget-event.php">Voransicht Terminbox</a>
    <br />
    <code class="inlinecode">
        &lt;iframe width="350" height="140" src="<?php echo Configure::read('AppConfig.serverName'); ?>/widgets/events?<span class="highlight">id=1234</span>"&gt;&lt;/iframe&gt;
    </code>

    <?php if ($loggedUser?->isOrga() || $loggedUser?->isAdmin()) { ?>
        <strong class="highlight">id=1234</strong>  / id= die ID deiner <?php echo Configure::read('AppConfig.initiativeNameSingular'); ?> ist zwingend erforderlich, du findest sie unter <a href="<?php echo $this->Html->urlUserWorkshopAdmin(); ?>">Meine Initiativen</a> in der Spalte ganz links<br />
    <?php } ?>

    <br />
    <strong class="highlight">Erweiterter Code:</strong><br />
    <code class="inlinecode">
        &lt;iframe width="350" height="140" src="<?php echo Configure::read('AppConfig.serverName'); ?>/widgets/events?id=<?php if(isset($wsuid)){ echo $wsuid; }else { echo '1234'; } ?>&<span class="highlight">num=2</span>&<span class="highlight">trunc=330px</span>"&gt;&lt;/iframe&gt;
    </code>
    <strong>Folgende optionale Parameter stehen zur Verfügung:</strong><br />
    <strong class="highlight">num=2</strong>  / num= die Anzahl der anzuzeigenden Termine<br />
    <strong class="highlight">trunc=300px</strong>  / trunc= die Breite der Termine in Pixel
    <br />
    <br />
    <strong class="highlight">Anpassen Aussehen:</strong><br />
    Die Ausgabe des gesamten Widgets steuerst du über die <a title="Mehr zu Iframes" rel="nofollow" target="_blank" href="https://developer.mozilla.org/de/docs/Web/HTML/Element/iframe">Parameter im Tag iframe</a> (width, height, frameborder ...) oder das Aussehen über sogenannte "Inline Styles" ,z.B. wie folgt:<br />
    <code class="inlinecode">
        &lt;iframe width="350" height="140" <span class="highlight">style="border:3px solid #000; background:#FFC;"</span> src="<?php echo Configure::read('AppConfig.serverName'); ?>/widgets/events?id=<?php if(isset($wsuid)){ echo $wsuid; }else { echo '1234'; } ?>&num=2&trunc=330px"&gt;&lt;/iframe&gt;
    </code>
    <br /><br />
</div>