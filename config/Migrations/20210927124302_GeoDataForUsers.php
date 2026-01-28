<?php
declare(strict_types=1);

use Migrations\BaseMigration;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;

class GeoDataForUsers extends BaseMigration
{
    public function change(): void
    {

        $this->execute("ALTER TABLE `users` ADD `lat` DOUBLE NULL DEFAULT NULL AFTER `country_code`, ADD `lng` DOUBLE NULL DEFAULT NULL AFTER `lat`;");

        $um = TableRegistry::getTableLocator()->get('Users');
        $users = $um->find('all',
            order: [
                'Users.uid' => 'ASC',
            ],
            conditions: [
                'Users.city <> ""',
            ],
        );

        /** @var \App\Model\Entity\User $user */
        foreach($users as $user) {

            $user->revertPrivatizeData();

            $address = $user->zip . ' ' . $user->city;
            if ($user->country_code != '') {
                $address .= ', ' . $user->country_code;
            }
            $address = Configure::read('AppConfig.htmlHelper')->replaceAddressAbbreviations($address);
            $address = trim($address);
            $baseUrl = 'https://maps.googleapis.com/maps/api/geocode/json?key='.Configure::read('googleMapApiKey').'&address=';
            $address = urlencode(utf8_encode($address));
            if ($address == '') {
                echo 'no address given for ' . $user->uid . ' ' . $user->name . LF;
                continue;
            }
            $geocodeAddress = json_decode(file_get_contents($baseUrl . $address));
            if ($geocodeAddress->status == 'OK' && empty($geocodeAddress->results[0]->partial_match)) {
                $user->lat = $geocodeAddress->results[0]->geometry->location->lat;
                $user->lng = $geocodeAddress->results[0]->geometry->location->lng;
                echo $user->uid . ' ' . $user->name . ' - ' . $address . ': ' . $user->lat . ' / ' . $user->lng . LF;
                $um->save($user, ['validate' => false]);
            } else {
                echo 'no geo data found: ' . $user->uid . ' ' . $user->name . ' - ' . $address . LF;
            }

            sleep(1);

        }

    }

}
