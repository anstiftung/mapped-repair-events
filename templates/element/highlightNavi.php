<?php

if (isset($main)) {
    $this->element('addScript', ['script' => "
        var activeElement = $('#nav ul li a').filter(function() {
            return $(this).text().toLowerCase() == '" . $main . "'.toLowerCase();
        });
        activeElement.addClass('active');
    "]);
    
    if (!$this->request->getSession()->read('isMobile')) {
        $this->element('addScript', ['script' => "
            MappedRepairEvents.Helper.showSubNavi(activeElement);
            MappedRepairEvents.Helper.initSubNavi();
        "]);
    }
}

?>