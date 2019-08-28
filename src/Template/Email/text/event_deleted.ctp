<?php use Cake\Core\Configure; ?>
Eine von dir abonnierte <?php echo Configure::read('AppConfig.initiativeNameSingular'); ?> hat einen Termin gelöscht. 

Klicke hier für aktuelle Reparaturtermine der Initiative:

<?php echo $domain,$url; ?>


<?php echo __('To remove your email from the subscription list, you can click here:');?>

<?php echo $domain,'/initiativen/newsunsub/',$unsub; ?>


Danke.
