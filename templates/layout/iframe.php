<!DOCTYPE html>
<html lang="de">
    <head>
    <meta charset="utf-8" />

    <?php
        $defaultMetaTags = [
             'autor' => Configure::read('AppConfig.titleSuffix')
            ,'language' => 'de'
            ,'description' => Configure::read('AppConfig.titleSuffix')
            ,'robots' => 'noindex, nofollow'
        ];
        if (!empty($metaTags)) {
            $metaTags = array_merge($defaultMetaTags, $metaTags);
        } else {
            $metaTags = $defaultMetaTags;
        }
        foreach($metaTags as $metaTagName => $metaTagContent) {
            echo $this->Html->meta(['name' => $metaTagName, 'content' => $metaTagContent])."\n";
        }
    ?>
    <title><?php echo $title_for_layout; ?> | <?php echo Configure::read('AppConfig.titleSuffix'); ?></title>
    <link rel="shortcut icon" href="/img/favicon.ico" type="image/x-icon" />

    <?php
        echo $this->AssetCompress->css('_iframe', ['raw' => true]);
        if (!empty($voting->custom_css)) {
            echo '<style>';
                echo $voting->custom_css;
            echo '</style>';
        }
    ?>

    <script type="text/javascript">
        if(!window.MappedRepairEvents) { MappedRepairEvents = window.MappedRepairEvents = {}; }
        var _gaq = window._gaq = _gaq || [];
    </script>

</head>

<body>

    <div id="content">
        <?php
            echo $this->Flash->render();
            echo $this->Flash->render('auth');
            echo $this->fetch('content');
        ?>
    </div>

    <div class="sc"></div>

    <?php
        echo $this->AssetCompress->script('_iframe', ['raw' => true]);
        // add script BEFORE all scripts that are loaded in views (block)
        echo $this->MyHtml->scriptBlock(
            JS_NAMESPACE . ".Helper.bindFlashMessageCancelButton();",
            ['inline' => true]
        );
        echo $this->fetch('script');
    ?>

</body>
</html>