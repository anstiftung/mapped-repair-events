<?php

use Cake\Core\Configure;

echo $this->fetch('content');
?>


Hilfe findest du hier:
<?php echo (Configure::read('AppConfig.isSsl') ? 'https' : 'http') . '://' . $_SERVER['SERVER_NAME']; ?>


PS: Diese E-Mail wurde automatisch erstellt.

<?php echo $this->element('email/signature'); ?>
