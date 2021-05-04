<?php use Cake\Core\Configure;

echo 'Die von dir abonnierte Initiative "' . $workshop->name . '" hat nächste Woche einen Reparatur-Termin:';


echo 'Bitte informiere dich bei der Reparatur-Initiative, unter welchen Bedingungen derzeit das Reparaturcafé angeboten wird. Kontaktdaten und weitere Informationen findest du hier:';

echo Configure::read('AppConfig.serverName').$url;


if ($event->is_online_event) {
    echo 'Der Reparatur-Termin findet online statt.';
}


echo __('To remove your email from the subscription list, you can click here:');

echo Configure::read('AppConfig.serverName').'/initiativen/newsunsub/',$unsub;



echo __('Thank you.');
