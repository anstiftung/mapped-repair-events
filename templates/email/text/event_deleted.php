<?php use Cake\Core\Configure; ?>
Eine von dir abonnierte Initiative hat einen Termin gelöscht.

Klicke hier für aktuelle Termine der Initiative:

<?php echo Configure::read('AppConfig.serverName') .$url; ?>


<?php echo __('To remove your email from the subscription list, you can click here:');?>

<?php echo Configure::read('AppConfig.serverName') . '/initiativen/newsunsub/' . $unsub; ?>


Danke.
