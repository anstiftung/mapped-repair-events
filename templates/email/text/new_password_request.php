<?php use Cake\Core\Configure; ?>
Hallo <?php echo $user->firstname; ?>,

du oder jemand anders hat um ein neues Passwort angefragt.

Hier ist es: <?php echo $password; ?>


Du kannst dich hier einloggen:
<?php echo Configure::read('AppConfig.serverName') . Configure::read('AppConfig.htmlHelper')->urlLogin('/users/passwortAendern'); ?>
