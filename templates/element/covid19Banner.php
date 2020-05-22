<?php

$covid19BannerCookie = $this->getRequest()->getCookie('covid_19_banner');
if (!empty($covid19BannerCookie)) {
    $value = json_decode($covid19BannerCookie);
    if (isset($value->show) && !$value->show) {
        return;
    }
}

$this->element('addScript', ['script' =>
    JS_NAMESPACE.".Helper.initCovid19Banner();
"]);

?>

<div id="covid-19-banner">
	<a href="javascript:void(0);"><i class="fa fa-times-circle fa-lg"></i></a>
	<img src="/img/core/covid-19-banner.jpg" width="250" height="209" />
</div>
