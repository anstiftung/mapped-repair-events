<?php
/**
 * @param string field (eg Post.image)
 * @param objectType
 * @param string image
 * @param int uid
 * @parms string label
 */

if (!empty($images)) {
    $this->element('addScript', ['script' =>
        JS_NAMESPACE.".Upload.setImages('".addslashes(json_encode($images))."');
    "]);
}

$this->element('addScript', ['script' =>
    JS_NAMESPACE.".Upload.updateImageCount(".(!empty($images) ? count($images) : 0).");
"]);

echo $this->element('upload/base', [
    'type' => 'multiple',
    'objectType' => $objectType,
    'uid' => $uid,
    'linkSrcForOverlay' => ''
]);

?>

<div class="input" style="width: 100%;">
    <label style="vertical-align: top;"><?php echo $label; ?></label>
    <?php
        echo $this->Html->link(
            '<i class="fas fa-images fa-border"></i>',
            'javascript:void(0);',
            [
                'class' => 'add-image-button multiple',
                'title' => 'Bilder hochladen',
                'escape' => false
            ]
        );
    ?>
</div>
