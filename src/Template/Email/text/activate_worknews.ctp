<?php use Cake\Core\Configure; ?>
Um dein Abonnement der Termine der <?php echo Configure::read('AppConfig.initiativeNameSingular'); ?> <?php echo $workshop->name; ?> zu aktivieren, klicke bitte auf den folgenden Link:

<?php echo $domain,'/initiativen/newsact/',$confirmationCode; ?>


Um deine E-Mailadresse aus der Abonnementliste für diese <?php echo Configure::read('AppConfig.initiativeNameSingular'); ?> zu entfernen, klicke bitte hier:

<?php echo $domain,'/initiativen/newsunsub/',$unsubscribeCode; ?>


Danke.
