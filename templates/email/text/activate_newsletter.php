<?php
    use Cake\Core\Configure;
?>
Um deinen Eintrag in den Newsletter zu bestätigen, klicke bitte auf den folgenden Link:
<?php echo Configure::read('AppConfig.serverName') . '/newsletter/activate/' . $confirmationCode; ?>


Um deine E-Mail-Adresse aus dem Newsletter zu entfernen, klicke bitte hier:
<?php echo Configure::read('AppConfig.serverName') . '/newsletter/unsubscribe/' . $unsubscribeCode; ?>


Hier kannst du unser Datenschutzbestimmungen einsehen:
<?php echo Configure::read('AppConfig.serverName') . $this->MyHtml->urlPageDetail('datenschutz'); ?>


Danke.
