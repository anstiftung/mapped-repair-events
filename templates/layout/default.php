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
    if (isset($canonicalUrl)) {
        echo '<link rel="canonical" href="'.$canonicalUrl.'" />';
    }
?>

<?php // generated with https://realfavicongenerator.net ?>
<link rel="apple-touch-icon" sizes="180x180" href="/img/apple-touch-icon.png">
<link rel="icon" type="image/png" sizes="32x32" href="/img/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="/img/favicon-16x16.png">
<link rel="manifest" href="/site.webmanifest">
<link rel="mask-icon" href="/img/safari-pinned-tab.svg" color="#5bbad5">
<link rel="shortcut icon" href="/img/favicon.ico">
<meta name="msapplication-TileColor" content="#da532c">
<meta name="msapplication-config" content="/browserconfig.xml">
<meta name="theme-color" content="#ffffff">

<meta name="application-name" content="<?php echo Configure::read('AppConfig.serverName'); ?>"/>
<meta name="msapplication-tooltip" content="<?php echo Configure::read('AppConfig.serverName'); ?>"/>
<meta name="apple-mobile-web-app-title" content="<?php echo Configure::read('AppConfig.serverName'); ?>"/>

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

            <?php $headerHeight = "135px"; ?>

            <?php if( $this->request->getSession()->read('isMobile') ) { ?>
                <div id="header" class="mobile" canvas="" style="height:75px;">
            <?php } else {?>
                <div id="header" class="no-mobile" style="height:<?php echo $headerHeight; ?>;">
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
                <div id="content" style="margin-top:<?php echo $headerHeight;?>;">
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