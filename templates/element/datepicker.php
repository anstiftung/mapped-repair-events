<?php
declare(strict_types=1);
$this->element('addScript', ['script' =>
    JS_NAMESPACE . ".Helper.initDatepicker();
    $('.datepicker-input').datepicker();
"]);
?>