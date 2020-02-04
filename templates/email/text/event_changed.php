<?php use Cake\Core\Configure;

echo 'Die von dir abonnierte Initiative "'.$workshop->name.'" hat einen Termin geändert.';?>


<?php echo __('Click here to learn more:'); ?>

<?php echo Configure::read('AppConfig.serverName').$url; ?>



<?php echo __('To remove your email from the subscription list, you can click here:');?>

<?php echo Configure::read('AppConfig.serverName').'/initiativen/newsunsub/',$unsub; ?>



<?php echo __('Thank you.');
