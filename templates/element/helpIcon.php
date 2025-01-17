<?php
declare(strict_types=1);
$this->element('addScript', ['script' =>
    JS_NAMESPACE.".AppFeatherlight.initLightboxForHref('a.help-link');"
]);
$id = 'id-' . rand(10000, 20000);
?>

<a href="#<?php echo $id;?>"
   class="inline help-link">
   <i class="fas fa-question fa-border"></i>
</a>

<div class="hide"><div id="<?php echo $id; ?>" class="help-layer">
    <?php echo $title; ?>
</div></div>