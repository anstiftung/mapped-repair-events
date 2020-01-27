Um deinen Eintrag in den Newsletter zu bestätigen, klicke bitte auf den folgenden Link:
<?php echo $domain,'/newsletter/activate/',$confirmationCode; ?>


Um deine E-Mail-Adresse aus dem Newsletter zu entfernen, klicke bitte hier:
<?php echo $domain,'/newsletter/unsubscribe/',$unsubscribeCode; ?>


Hier kannst du unser Datenschutzbestimmungen einsehen:
<?php echo $domain . $this->MyHtml->urlPageDetail('datenschutz'); ?>


Danke.
