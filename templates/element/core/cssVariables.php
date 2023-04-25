<?php
    use Cake\Core\Configure;
?>

<style>
    :root {
        --theme-color-a: <?php echo Configure::read('AppConfig.themeColorA'); ?>;
        --theme-color-b: <?php echo Configure::read('AppConfig.themeColorB'); ?>;
        --theme-color-c: <?php echo Configure::read('AppConfig.themeColorC'); ?>;
        --theme-color-d: <?php echo Configure::read('AppConfig.themeColorD'); ?>;
        --theme-color-e: <?php echo Configure::read('AppConfig.themeColorE'); ?>;
        --theme-color-f: <?php echo Configure::read('AppConfig.themeColorF'); ?>;
    }
</style>