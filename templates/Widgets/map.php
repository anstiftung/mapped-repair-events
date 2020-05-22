<?php
use Cake\Core\Configure;

$this->element('addScript', array('script' => "
    var mapObject = new ".JS_NAMESPACE.".Map([], 'search', true, ".json_encode($customCenterCoordinates).", ".$customZoomLevel.", '".$markerSrc."', '".$foundMarkerSrc."');
    mapObject.setMapAsFixed($('#content .right').height());"
));

$customCss = '';

if ($buttonMouseoverColor != '') {
    $customCss .= '#search:hover, .button:hover, a.button:hover { color: ' . $buttonMouseoverColor.' ! important;} ';
}
if ($buttonMouseoverBgColor != '') {
    $customCss .= '#search:hover, .button:hover, a.button:hover { background-color: ' . $buttonMouseoverBgColor.' ! important;} ';
}
if ($searchButtonColor != '') {
    $customCss .= '#search { color: ' . $searchButtonColor.' ! important;} ';
}
if ($searchButtonBgColor != '') {
    $customCss .= '#search { background-color: ' . $searchButtonBgColor.' ! important;} ';
}
if ($buttonColor != '') {
    $customCss .= '.button, a.button { color: ' . $buttonColor.' ! important;} ';
}
if ($buttonBgColor != '') {
    $customCss .= '.button, a.button { background-color: ' . $buttonBgColor.' ! important;} ';
}
if ($clusterOuterColor != '') {
    $customCss .= '.prunecluster { background-color: ' . $clusterOuterColor.' ! important;} ';
}
if ($clusterFontColor != '') {
    $customCss .= '.prunecluster { color: ' . $clusterFontColor.' ! important;} ';
}
if ($clusterInnerColor != '') {
    $customCss .= '.prunecluster div { background-color: ' . $clusterInnerColor.' ! important;} ';
}

if ($customCss != '') {
    echo '<style>'.$customCss.'</style>';
}

?>

<div class="right">

    <div class="box filter">
        <span class="wss">Ort</span>

        <div class="input">
            <input name="data[workshopSearchAddress]" id="workshopSearchAddress" type="text" value="" />
        </div>

        <button id="search" type="button" class="button submit">Suche</button>
        <button id="reset" type="button" class="button gray reset-widget">Suche zurücksetzen</button>

        <a class="button gray plattform-link" target="_blank" href="<?php echo Configure::read('AppConfig.serverName'); ?>">
            <img src="/img/core/logo-grau.jpg" width="60" height="60" />
            <span>zu <?php echo $this->Html->getHostName(); ?></span>
        </a>

    </div>

    <div class="sc"></div>

    <div id="mapContainer" >
        <div id="map" >
            <div id="workshopSearchLoader"></div>
        </div>
    </div>

</div>