<?php
use Cake\Core\Configure;
?>

<div id="navToggle"></div>

<div id="logoouter">
<a href="/" title="<?php echo Configure::read('AppConfig.initiativeNamePlural'); ?> finden, unterstützen und gründen">
    <img id="logo" alt="Logo <?php echo Configure::read('AppConfig.platformName'); ?>" src="/img/core/logo.jpg"  />
</a>
</div>

<div id="claim"><?php echo Configure::read('AppConfig.claim'); ?></div>

<?php
    echo $this->element('core/loginBox');
?>

<nav off-canvas="main-menu left reveal" id="nav" role="navigation">
    <?php echo $this->element('core/navi'); ?>
</nav>

<div class="sc"></div>

<div class="socialicons">
    <?php if (Configure::read('AppConfig.facebookUsername') != '') { ?>
        <a href="<?php echo $this->Html->getFacebookUrl(Configure::read('AppConfig.facebookUsername')); ?>" target="_blank" title="<?php echo Configure::read('AppConfig.platformName'); ?> auf Facebook">
            <i class="fab fa-facebook-square"></i>
        </a>
    <?php } ?>
    <?php if (Configure::read('AppConfig.twitterUsername') != '') { ?>
        <a href="https://twitter.com/<?php echo Configure::read('AppConfig.twitterUsername'); ?>" target="_blank" title="<?php echo Configure::read('AppConfig.platformName'); ?> auf Twitter">
            <i class="fab fa-twitter-square"></i>
        </a>
    <?php } ?>
</div>
