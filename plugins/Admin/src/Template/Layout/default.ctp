<?php
use Cake\Core\Configure;
use Cake\Utility\Inflector;
?>
<!DOCTYPE html>
<html lang="de">  
    <head>
    <meta charset="utf-8" />
    <?php
        $defaultMetaTags = [
            'title' => !empty($defaultMetaTags) && !empty($defaultMetaTags['title']) ? $defaultMetaTags['title'] : '',
            'description' => !empty($defaultMetaTags) && !empty($defaultMetaTags['description']) ? $defaultMetaTags['description'] : Configure::read('AppConfig.titleSuffix'),
            'keywords' => !empty($defaultMetaTags) && !empty($defaultMetaTags['keywords']) ? $defaultMetaTags['keywords'] : ''
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
    ?>
  
    <?php
        echo $this->AssetCompress->css('_admin', array('raw' => Configure::read('debug')));
    ?>
    
    <script type="text/javascript">
        if(!window.<?php echo JS_NAMESPACE; ?>) { <?php echo JS_NAMESPACE; ?> = window.<?php echo JS_NAMESPACE; ?> = {}; }
    </script>

</head>

<body class="<?php echo Inflector::tableize($this->name) . ' ' . $this->request->getParam('action'); ?>">
  
  <div id="wrapper">
  
    <div id="header">
      <?php echo $this->element('core/header'); ?>
      <?php echo $this->element('core/loginBox'); ?>
      <div class="sc"></div>
    </div>
  
    <div id="content">
        <?php
          echo $this->Flash->render();
          echo $this->Flash->render('auth');
          echo $this->fetch('content');
      ?>
    </div>
    
    <div class="sc"></div>    
  
  </div>
  
  <div class="sc"></div>
    
  <?php
    echo $this->AssetCompress->script('_admin', array('raw' => Configure::read('debug')));
      
    echo $this->Html->script('/node_modules/ckeditor/ckeditor');
    echo $this->Html->script('/node_modules/ckeditor/adapters/jquery');
    
    echo $this->Html->scriptBlock(
        $this->Html->wrapJavascriptBlock(
            JS_NAMESPACE.".Admin.init();"
        ),
        ['inline' => true]
    );
      
    $scripts = $this->fetch('script');
    if ($scripts != '') {
        echo $this->Html->wrapJavascriptBlock($scripts);
    }
  ?>

</body>
</html>