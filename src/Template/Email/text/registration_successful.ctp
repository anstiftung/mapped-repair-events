<?php use Cake\Core\Configure; ?>
Hallo <?php echo $data['Users']['firstname']; ?>,

vielen Dank für deine Anmeldung bei <?php echo $this->MyHtml->getHostName(); ?>.

<?php if ( !empty($password) ): ?>Dein vorläufiges Passwort lautet: <?php echo $password; ?>
<?php endif; ?>


Bitte melde dich über den folgenden Link auf der Plattform an, um deine E-Mail-Adresse zu bestätigen:
<?php echo (Configure::read('isSsl') ? 'https' : 'http'); ?>://<?php echo Configure::read('AppConfig.serverName'); ?>/users/activate/<?php echo $data['Users']['confirm']; ?>


Vielen Dank für Dein Engagement!

