<?php
$this->element('addScript', ['script' =>
    JS_NAMESPACE . ".Helper.initDatepicker();
    $('.datepicker-input').datepicker();
"]);
?>