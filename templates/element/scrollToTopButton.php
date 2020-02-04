<?php
  $this->element('addScript', ['script' =>
    JS_NAMESPACE.".Helper.initScrollToTopButton();
  "]);
?>

<div id="scroll-to-top">
    <a href="javascript:void(0);"><i class="fas fa-arrow-alt-circle-up"></i></a>
</div>
