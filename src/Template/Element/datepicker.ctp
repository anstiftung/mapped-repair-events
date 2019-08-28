<?php
/**
 * @param array fields
 */

$this->element('addScript', ['script' => 
    JS_NAMESPACE.".Helper.initDatepicker();
"]);

foreach($fields as $field) {
  $this->element('addScript', ['script' => "
    $(function() {
      $('#".$field."').datepicker()
    });
  "]);
}
?>