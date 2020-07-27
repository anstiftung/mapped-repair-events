<?php use Cake\Core\Configure;

echo 'Die von dir abonnierte Initiative "' . $workshop->name . '" hat nächste Woche einen Reparatur-Termin:'; ?>


<?php echo 'Bitte informiere dich bei der Reparatur-Initiative, unter welchen Bedingungen derzeit das Reparaturcafé angeboten wird. Kontaktdaten und weitere Informationen findest du hier:'; ?>

<?php echo Configure::read('AppConfig.serverName').$url; ?>



<?php echo __('To remove your email from the subscription list, you can click here:');?>

<?php echo Configure::read('AppConfig.serverName').'/initiativen/newsunsub/',$unsub; ?>



<?php echo __('Thank you.');
