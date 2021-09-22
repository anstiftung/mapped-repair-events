<?php use Cake\Core\Configure; ?>
Hallo,

<?php echo $user->firstname . ' ' . $user->lastname;?> (<?php echo $user->email; ?>) möchte bei deiner <?php echo Configure::read('AppConfig.initiativeNameSingular'); ?> "<?php echo $workshop->name; ?>" mitmachen.

Du kannst die Anfrage hier bestätigen (dazu du musst eingeloggt sein):

<?php echo Configure::read('AppConfig.serverName') . $this->MyHtml->urlUserWorkshopAdmin(); ?>
