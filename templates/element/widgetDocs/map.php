<?php
use Cake\Core\Configure;
?>

<div href="#collapse2" class="box-toggle box-toggle2">
    <?php echo __('Widgetpage: Map Integration'); ?>
</div>
<div id="collapse2" class="collapse">
    <h2>Schnell-Einbindung</h2>
    Kopiere den folgenden Code, um die komplette Landkarte aller <?php echo Configure::read('AppConfig.initiativeNamePlural'); ?> auf deiner eigenen Webseite anzuzeigen:
    <br />
    <strong class="highlight">Beispiel-Link:</strong> <a title="Voransicht" target="_blank" href="/widgets/test-widget-map.php">Voransicht Landkarte</a>
    <br />
    <code class="inlinecode">
        &lt;iframe frameborder="0" width="100%" height="650" src="<?php echo Configure::read('AppConfig.serverName'); ?>/widgets/map"&gt;&lt;/iframe&gt;
    </code>

    <br />
    <h2>Zoom auf bestimmten Bereich (Land, Region)</h2>
    Du kannst auch Koordinaten für eine fixe Zentrierung der Karte, sowie ein vordefiniertes Zoom-Level angeben. Die geographischen Daten für deine Stadt, dein Bundesland oder dein Land findest du zum Beispiel auf <a href="http://www.latlong.net" target="_blank">dieser Webseite</a>.<br />
    <strong class="highlight">Beispiel-Link für die Zentrierung auf die Schweiz:</strong> <a title="Voransicht" target="_blank" href="/widgets/test-widget-map-switzerland.php">Voransicht Landkarte</a>
    <br />
    <code class="inlinecode">
        &lt;iframe frameborder="0" width="100%" height="650" src="<?php echo Configure::read('AppConfig.serverName'); ?>/widgets/map?<span class="highlight">zoomLevel=8</span>&amp;<span class="highlight">lat=46.8</span>&amp;<span class="highlight">lng=8.2</span>"&gt;&lt;/iframe&gt;
    </code>

    <br />
    <h2>Farben anpassen / eigene Marker verwenden</h2>
    <ul>
        <li>Hex-Codes für die Farben bitte <b>ohne #</b> angeben.</li>
        <li>Hier findest du <a href="https://www.iconfinder.com/search/?q=marker&license=1&price=free" target="_blank">viele frei verfügbare Marker.</a></li>
        <li>Der Parameter "markerSrc" ist die Standard-Grafik für die angezeigten Initiativen, "foundMarkerSrc" wird für die Suchergebnisse verwendet.</li>
        <li>Die Marker-Grafiken können auch einen Schatten enthalten (png) und müssen über einen HTTPS-Server bereitgestellt werden.</li>
        <li>Die Parameter <b>zoomLevel, lat und lng</b> können mit &amp; an den letzten Parameter angehängt werden.</li>
    </ul>
    <strong class="highlight">Beispiel-Link für eine umgestaltete Karte:</strong> <a title="Voransicht" target="_blank" href="/widgets/test-widget-map-custom-colors-and-marker.php">Voransicht Landkarte</a>
    <br />
    <code class="inlinecode">
        &lt;iframe frameborder="0" width="100%" height="650" src="<?php echo Configure::read('AppConfig.serverName'); ?>/widgets/map?
        <span class="highlight">buttonColor=4f0901</span>&amp;<span class="highlight">buttonBgColor=db0202</span>&amp;<span class="highlight">searchButtonColor=db0202</span>&amp;<span class="highlight">searchButtonBgColor=4f0901</span>&amp;<span class="highlight">buttonMouseoverBgColor=ccc</span>&amp<span class="highlight">buttonMouseoverColor=000</span><br />
        &amp;<span class="highlight">clusterOuterColor=4f0901</span>&amp;<span class="highlight">clusterInnerColor=E22F00</span>&amp;<span class="highlight">clusterFontColor=4f0901</span><br />
        &amp;<span class="highlight">markerSrc=https://cdn2.iconfinder.com/data/icons/splashyIcons/marker_squared_red.png</span><br />&amp;<span class="highlight">foundMarkerSrc=https://cdn2.iconfinder.com/data/icons/splashyIcons/marker_squared_grey_5.png</span>"
        &gt;&lt;/iframe&gt;
    </code>

    <br /><br />
</div>