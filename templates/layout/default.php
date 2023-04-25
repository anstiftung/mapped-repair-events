<?php
use Cake\Core\Configure;
use Cake\Utility\Inflector;
?>
<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="utf-8" />
<meta name="handheldfriendly" content="True" />
<meta name="mobileoptimized" content="320" />
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
<meta name="apple-mobile-web-app-capable" content="True" />
<meta name="apple-mobile-web-app-status-bar-style" content="black" />
<meta name="dcterms.rightsHolder" content="Stiftungsgemeinschaft anstiftung&amp;ertomis gemeinnützige GmbH">
<meta name="dcterms.dateCopyrighted" content="2015">

<?php
    $defaultMetaTags = [
        'title' => !empty($defaultMetaTags) && !empty($defaultMetaTags['title']) ? $defaultMetaTags['title'] : '',
        'description' => !empty($defaultMetaTags) && !empty($defaultMetaTags['description']) ? $defaultMetaTags['description'] : Configure::read('AppConfig.titleSuffix'),
        'keywords' => !empty($defaultMetaTags) && !empty($defaultMetaTags['keywords']) ? $defaultMetaTags['keywords'] : '',
        'csrfToken' => $this->request->getAttribute('csrfToken'),
    ];
    if (Configure::read('debug') === true) {
        $defaultMetaTags['robots'] = 'noindex, nofollow';
    }
    if (!empty($metaTags)) {
        $metaTags = array_merge($defaultMetaTags, $metaTags);
    } else {
        $metaTags = $defaultMetaTags;
    }
    foreach($metaTags as $metaTagName => $metaTagContent) {
        if ($metaTagName == 'title') continue;
        echo $this->Html->meta(['name' => $metaTagName, 'content' => $metaTagContent])."\n";
    }
?>
<title><?php echo $metaTags['title'] . ' | ' . Configure::read('AppConfig.titleSuffix'); ?></title>
<?php
    echo $this->Html->meta('favicon.ico','img/favicon.ico',['type' => 'icon']);
    if (isset($canonicalUrl)) {
        echo '<link rel="canonical" href="'.$canonicalUrl.'" />';
    }
?>
<link rel="icon" type="image/x-icon" href="/img/favicon.ico"/>
<link rel="icon" type="image/gif" href="/img/favicon.gif"/>
<link rel="icon" type="image/png" href="/img/favicon.png"/>
<link rel="apple-touch-icon" href="/img/apple-touch-icon.png"/>
<link rel="apple-touch-icon" href="/img/apple-touch-icon-57x57.png" sizes="57x57"/>
<link rel="apple-touch-icon" href="/img/apple-touch-icon-60x60.png" sizes="60x60"/>
<link rel="apple-touch-icon" href="/img/apple-touch-icon-72x72.png" sizes="72x72"/>
<link rel="apple-touch-icon" href="/img/apple-touch-icon-76x76.png" sizes="76x76"/>
<link rel="apple-touch-icon" href="/img/apple-touch-icon-114x114.png" sizes="114x114"/>
<link rel="apple-touch-icon" href="/img/apple-touch-icon-120x120.png" sizes="120x120"/>
<link rel="apple-touch-icon" href="/img/apple-touch-icon-128x128.png" sizes="128x128"/>
<link rel="apple-touch-icon" href="/img/apple-touch-icon-144x144.png" sizes="144x144"/>
<link rel="apple-touch-icon" href="/img/apple-touch-icon-152x152.png" sizes="152x152"/>
<link rel="apple-touch-icon" href="/img/apple-touch-icon-180x180.png" sizes="180x180"/>
<link rel="apple-touch-icon" href="/img/apple-touch-icon-precomposed.png"/>
<link rel="icon" type="image/png" href="/img/favicon-16x16.png" sizes="16x16"/>
<link rel="icon" type="image/png" href="/img/favicon-32x32.png" sizes="32x32"/>
<link rel="icon" type="image/png" href="/img/favicon-96x96.png" sizes="96x96"/>
<link rel="icon" type="image/png" href="/img/favicon-160x160.png" sizes="160x160"/>
<link rel="icon" type="image/png" href="/img/favicon-192x192.png" sizes="192x192"/>
<link rel="icon" type="image/png" href="/img/favicon-196x196.png" sizes="196x196"/>
<meta name="msapplication-TileImage" content="/img/win8-tile-144x144.png"/>
<meta name="msapplication-TileColor" content="#c8d218"/>
<meta name="msapplication-navbutton-color" content="#c8d217"/>
<meta name="application-name" content="<?php echo Configure::read('AppConfig.serverName'); ?>"/>
<meta name="msapplication-tooltip" content="<?php echo Configure::read('AppConfig.serverName'); ?>"/>
<meta name="apple-mobile-web-app-title" content="<?php echo Configure::read('AppConfig.serverName'); ?>"/>
<meta name="msapplication-square70x70logo" content="/img/win8-tile-70x70.png"/>
<meta name="msapplication-square144x144logo" content="/img/win8-tile-144x144.png"/>
<meta name="msapplication-square150x150logo" content="/img/win8-tile-150x150.png"/>
<meta name="msapplication-wide310x150logo" content="/img/win8-tile-310x150.png"/>
<meta name="msapplication-square310x310logo" content="/img/win8-tile-310x310.png"/>

<?php echo $this->element('core/cssVariables'); ?>

<?php
    echo $this->AssetCompress->css('_frontend' . ($this->request->getSession()->read('isMobile') ? '_mobile' : ''), ['raw' => Configure::read('debug')]);
?>

<script type="text/javascript">
    if(!window.<?php echo JS_NAMESPACE; ?>) { <?php echo JS_NAMESPACE; ?> = window.<?php echo JS_NAMESPACE; ?> = {}; }
</script>

</head>

<body class="<?php echo Inflector::tableize($this->name) . ' ' . $this->request->getParam('action'); ?><?php echo !empty($page->url) ? ' ' . $page->url : ''; ?>">

    <div id="everything">

        <div id="wrapper">

            <?php if( $this->request->getSession()->read('isMobile') ) { ?>
                <div id="header" class="mobile" canvas="" style="height:75px;">
            <?php } else {?>
                <div id="header" class="no-mobile" style="height:120px;">
            <?php } ?>

            <?php
                if (empty($loggedUser)) {
                    $this->element('addScript', ['script' =>
                        JS_NAMESPACE.".Helper.doLoginFormActions();
                    "]);
                }
                echo $this->element('core/header');
            ?>
            </div>

            <?php if( $this->request->getSession()->read('isMobile') ) { ?>
                <div canvas="container" id="content" style="margin-top:75px;">
            <?php } else {?>
                <div id="content" style="margin-top:120px;">
            <?php } ?>

            <?php
                echo $this->Flash->render();
                echo $this->Flash->render('auth');
                echo $this->fetch('content');
            ?>

            </div>

            <div class="sc"></div>
            <div id="footer" canvas="container">
                <?php echo $this->element('core/footer'); ?>
            </div>

            <?php
                if (!$this->request->getSession()->read('isMobile')) {
                    echo $this->element('scrollToTopButton');
                }
            ?>

            <?php

            echo $this->AssetCompress->script('_frontend' . ($this->request->getSession()->read('isMobile') ? '_mobile' : ''), ['raw' => Configure::read('debug')]);

            echo $this->Html->script('/node_modules/ckeditor4/ckeditor.js?v4.19.0');
            echo $this->Html->script('/node_modules/ckeditor4/adapters/jquery.js?v4.19.0');

            // add script BEFORE all scripts that are loaded in views (block)
            echo $this->Html->scriptBlock(
                $this->Html->wrapJavascriptBlock(
                    JS_NAMESPACE.".Helper.init();"
                ),
                ['inline' => true]
            );
            if ($this->request->getSession()->read('isMobile')) {
                echo $this->Html->scriptBlock(
                    $this->Html->wrapJavascriptBlock(
                        JS_NAMESPACE.".Helper.initMobile();"
                    ),
                    ['inline' => true]
                );
            } else {
                echo $this->Html->scriptBlock(
                    $this->Html->wrapJavascriptBlock(
                        JS_NAMESPACE.".Helper.initSubNavi();"
                    ),
                    ['inline' => true]
                );
            }

            $scripts = $this->fetch('script');
            if ($scripts != '') {
                echo $this->Html->wrapJavascriptBlock($scripts);
            }
            ?>

        </div><!--wrapper-->

    </div><!--everything-->

    <?php echo $this->element('core/matomo'); ?>

</body>
</html>