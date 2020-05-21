<?php
    if (isset($_SESSION['show_covid_19_banner']) && !$_SESSION['show_covid_19_banner']) {
        return;
    }
    
    $this->element('addScript', ['script' =>
        JS_NAMESPACE.".Helper.initCovid19Banner();
    "]);

?>

<div id="covid-19-banner">
	<a href="javascript:void(0);"><i class="fa fa-times-circle fa-lg"></i></a>
	<img src="/img/core/covid-19-banner.jpg" width="250" height="209" />
</div>
