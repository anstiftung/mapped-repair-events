<?php use Cake\Core\Configure; ?>
Hallo,

der Organisator <?php echo $username; ?> hat soeben die <?php echo Configure::read('AppConfig.initiativeNameSingular'); ?> <?php echo $workshop->name; ?> erstellt.
