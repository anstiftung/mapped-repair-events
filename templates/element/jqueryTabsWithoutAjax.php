<?php
declare(strict_types=1);
$this->element('addScript', ['script' =>
    JS_NAMESPACE.".Helper.initJqueryTabsWithoutAjax();
"]);
?>

<div id="tabs" class="custom-ui-tabs ui-tabs ui-widget ui-widget-content ui-corner-all" style="background: none;">
    <ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all" role="tablist">
        <?php
            if (!isset($selected)) {
                $selected = $this->request->getAttribute('here');
            }
            foreach($links as $link) {
                $classes = ['ui-state-default', 'ui-corner-top'];
                if ($selected == $link['url']) {
                    $classes[] = 'ui-tabs-active';
                    $classes[] = 'ui-state-active';
                }
                echo '<li class="'.implode(' ', $classes).'"><a href="'.$link['url'].'" class="ui-tabs-anchor">'.$link['name'].'</a></li>';
            }
        ?>
    </ul>
</div>
