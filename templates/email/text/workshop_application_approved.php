<?php use Cake\Core\Configure; ?>
Hallo <?php echo $userEntity->name; ?>,

deine Anfrage zur Mitarbeit bei der <?php echo Configure::read('AppConfig.initiativeNameSingular'); ?> "<?php echo $workshop->name; ?>" wurde soeben bestätigt.

Hier gelangst du zur Profil-Seite der <?php Configure::read('AppConfig.initiativeNameSingular'); ?>:

<?php echo Configure::read('AppConfig.serverName') . $this->MyHtml->urlWorkshopDetail($workshop->url); ?>
