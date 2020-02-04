<?php use Cake\Core\Configure; ?>
Eine von dir abonnierte <?php echo Configure::read('AppConfig.initiativeNameSingular'); ?> hat einen Termin gelöscht. 

Klicke hier für aktuelle Reparaturtermine der Initiative:

<?php echo Configure::read('AppConfig.serverName') .$url; ?>


<?php echo __('To remove your email from the subscription list, you can click here:');?>

<?php echo Configure::read('AppConfig.serverName') . '/initiativen/newsunsub/' . $unsub; ?>


Danke.
