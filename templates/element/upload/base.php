<?php
$this->element('addScript', ['script' =>
    JS_NAMESPACE.".Upload.appendFormToBody('".$objectType."', '".$uid."', '".$linkSrcForOverlay."', '" . $type . "');".
    JS_NAMESPACE.".Upload.init('.add-image-button." . $type . "', '".$type."');
"]);
?>