<?php use Cake\Core\Configure; ?>

Um dein Abonnement der Termine der Initiative <?php echo $workshop->name; ?> zu aktivieren, klicke bitte auf den folgenden Link:

<?php echo Configure::read('AppConfig.serverName') . '/initiativen/newsact/' . $confirmationCode; ?>


Um deine E-Mailadresse aus der Abonnementliste für diese Initiative zu entfernen, klicke bitte hier:

<?php echo Configure::read('AppConfig.serverName') . '/initiativen/newsunsub/' . $unsubscribeCode; ?>


Danke.
