<?php
use Cake\Core\Configure;
?>

<div href="#collapse3" class="box-toggle box-toggle3">
    Integration Statistik
</div>
<div id="collapse3" class="collapse">
    Kopiere den folgenden Code, um die die Statistik aller <?php echo Configure::read('AppConfig.initiativeNamePlural'); ?> auf deiner eigenen Webseite anzuzeigen:
    <br />
    <br />

    <h2>Globale Statistik: Schnell-Einbindung</h2>
    <strong class="highlight">Beispiel-Link:</strong> <a title="Voransicht" target="_blank" href="/widgets/test-widget-statistic-global.php">Voransicht Statistik Global</a>

    <code class="inlinecode">
        &lt;iframe frameborder="0" width="100%" height="700" src="<?php echo Configure::read('AppConfig.serverName'); ?>/widgets/statistics-global"&gt;&lt;/iframe&gt;
    </code>

    <h2>Globale Statistik: Standard-Datenquelle auswählen</h2>
    <ul>
        <li>Die Standard-Datenquelle kann aus folgenden Quellen ausgewählt werden: 'all', 'third-party-name', 'platform'</li>
    </ul>

    <code class="inlinecode">
        &lt;iframe frameborder="0" width="100%" height="700" src="<?php echo Configure::read('AppConfig.serverName'); ?>/widgets/statistics-global?<span class="highlight">defaultDataSource=platform</span>"&gt;&lt;/iframe&gt;
    </code>

    <br />
    <h2><?php echo Configure::read('AppConfig.platformName'); ?>-Statistik: Schnell-Einbindung</h2>
    <strong class="highlight">Beispiel-Link:</strong> <a title="Voransicht" target="_blank" href="/widgets/test-widget-statistic-workshop.php">Voransicht Statistik <?php echo Configure::read('AppConfig.initiativeNameSingular'); ?></a>

    <code class="inlinecode">
        &lt;iframe frameborder="0" width="100%" height="700" src="<?php echo Configure::read('AppConfig.serverName'); ?>/widgets/statistics-workshop/<span class="highlight">1234</span>"&gt;&lt;/iframe&gt;
    </code>

    <?php if ($appAuth->isOrga() || $appAuth->isAdmin()) { ?>
        <strong class="highlight">1234</strong>  / die ID deiner <?php echo Configure::read('AppConfig.initiativeNameSingular'); ?> ist zwingend erforderlich, du findest sie unter <a href="<?php echo $this->Html->urlUserWorkshopAdmin(); ?>">Meine Initiativen</a> in der Spalte ganz links<br />
    <?php } ?>

    <br />
    <h2>Farben anpassen</h2>
    <ul>
        <li>Die Hintergrund- und Rahmenfarben können angepasst werden. Die Farb-Codes bitte entweder als Hex-Code <b>ohne #</b> oder als RGB / RGBA (wie im Beispiel) angeben.</li>
    </ul>

    <code class="inlinecode">
        &lt;iframe frameborder="0" width="100%" height="700" src="<?php echo Configure::read('AppConfig.serverName'); ?>/widgets/statistics-global?<span class="highlight">borderColorOk=rgb(14,113,184)</span>&<span class="highlight">backgroundColorOk=rgba(14,113,184,0.6)</span>&<span class="highlight">borderColorNotOk=rgb(181,24,33)</span>&<span class="highlight">backgroundColorNotOk=rgba(181,24,33,0.6)</span>&<span class="highlight">borderColorRepairable=rgb(242,217,164)</span>&<span class="highlight">backgroundColorRepairable=rgba(242,217,164,0.6)</span>"&gt;&lt;/iframe&gt;
    </code>

    <br />
    <h2><?php echo Configure::read('AppConfig.initiativeNamePlural'); ?>-Name ausblenden</h2>
    <ul>
        <li>Beim Widget für <?php echo Configure::read('AppConfig.initiativeNamePlural'); ?> kann der Name der Initiative ausgeblendet werden.</li>
    </ul>

    <code class="inlinecode">
        &lt;iframe frameborder="0" width="100%" height="700" src="<?php echo Configure::read('AppConfig.serverName'); ?>/widgets/statistics-workshop/1234?<span class="highlight">showWorkshopName=0</span>"&gt;&lt;/iframe&gt;
    </code>

    <br />
    <h2>Umweltentlastung ausblenden</h2>
    <ul>
        <li>Beim Widget für <?php echo Configure::read('AppConfig.initiativeNamePlural'); ?> kann die Umweltentlastung in Form von eingesparten Flug-Kilometer ausgeblendet werden. Die Umweltentlastung wird nie angezeigt, wenn nur das Donut-Diagramm angezeigt wird.</li>
    </ul>

    <code class="inlinecode">
        &lt;iframe frameborder="0" width="100%" height="700" src="<?php echo Configure::read('AppConfig.serverName'); ?>/widgets/statistics-workshop/1234?<span class="highlight">showCarbonFootprint=0</span>"&gt;&lt;/iframe&gt;
    </code>

    <br />
    <h2>Balkendiagramm bzw. Donut-Diagramm ausblenden</h2>
    <ul>
        <li>showDonutChart / showBarChart auf <b>0</b> setzen, falls die enstprechende Grafik nicht angezeigt werden soll.</li>
        <li>Hinweis: Das Donut-Diagramm wird beim globalen Statistik-Widget nur dann angezeigt, wenn beim Dropdown Datenquelle der Wert "<?php echo Configure::read('AppConfig.platformName'); ?>" ausgewählt ist.</li>
    </ul>
    <code class="inlinecode">
        &lt;iframe frameborder="0" width="100%" height="700" src="<?php echo Configure::read('AppConfig.serverName'); ?>/widgets/statistics-global?<span class="highlight">?showDonutChart=0</span>"&gt;&lt;/iframe&gt;
    </code>

</div>