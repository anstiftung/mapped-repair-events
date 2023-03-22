<?php use Cake\Core\Configure; ?>
Hallo,

<?php echo $username; ?> hat soeben die Initiative "<?php echo $workshop->name; ?>" <?php echo $userAction; ?>.

<?php echo Configure::read('AppConfig.serverName') . $this->MyHtml->urlWorkshopDetail($workshop->url); ?>
