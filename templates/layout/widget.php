<?php
declare(strict_types=1);
    use Cake\Core\Configure;
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8" />

    <script type="text/javascript">
        if(!window.<?php echo JS_NAMESPACE; ?>) { <?php echo JS_NAMESPACE; ?> = window.<?php echo JS_NAMESPACE; ?> = {}; }
    </script>

    <?php echo $this->element('core/cssVariables'); ?>

    <?php
        echo $this->AssetCompress->css('_widget-' . $assetNamespace, ['raw' => Configure::read('debug')]);
    ?>

</head>
<body style="background:none;">

    <div id="content">
        <?php echo $this->fetch('content'); ?>
    </div>

    <?php
        echo $this->AssetCompress->script('_widget-' . $assetNamespace, ['raw' => Configure::read('debug')]);

        // add script BEFORE all scripts that are loaded in views (block)
        echo $this->Html->scriptBlock(
            $this->Html->wrapJavascriptBlock(
                JS_NAMESPACE.".Helper.highlightFormFields();",
                ['inline' => true]
            )
        );

        $scripts = $this->fetch('script');
        if ($scripts != '') {
            echo $this->Html->wrapJavascriptBlock($scripts);
        }

    ?>

</body>
</html>