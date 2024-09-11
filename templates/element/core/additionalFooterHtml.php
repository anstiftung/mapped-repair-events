<?php
if (!(!$this->request->getSession()->read('isMobile') && empty($loggedUser))) {
?>
    <a href="https://anstiftung.de/praxis/reparieren" title="anstiftung - Offene Werkstätten, Interkulturelle und Urbane Gemeinschaftsgärten" target="_blank"><div id="aslog"></div></a>
<?php
}
?>
