<?php use Cake\Core\Configure; ?>
Hallo <?php echo $user->firstname; ?>,

du oder jemand anders hat um ein neues Passwort angefragt.

Hier ist es: <?php echo $password; ?>


Du kannst dich hier einloggen:
https://<?php echo $_SERVER['SERVER_NAME'] . Configure::read('AppConfig.htmlHelper')->urlLogin('/users/passwortAendern'); ?>
