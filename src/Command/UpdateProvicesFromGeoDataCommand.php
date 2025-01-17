<?php
declare(strict_types=1);

namespace App\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use App\Services\GeoService;
use Cake\ORM\TableRegistry;

class UpdateProvicesFromGeoDataCommand extends Command
{

    public function execute(Arguments $args, ConsoleIo $io)
    {

        $geoService = new GeoService();
        
        $provincesTable = TableRegistry::getTableLocator()->get('Provinces');
        $provincesMap = $provincesTable->find('list', keyField: 'id', valueField: 'name')->toArray();

        $workshopsTable = TableRegistry::getTableLocator()->get('Workshops');
        $workshops = $workshopsTable->find('all')->where(
            [
                $workshopsTable->aliasField('province_id') => 0,
            ]
        )->orderBy([
            $workshopsTable->aliasField('uid') => 'ASC']
        );

        foreach($workshops as $workshop) {
            $geoData = $geoService->getGeoDataByCoordinates($workshop->lat, $workshop->lng);
            $workshop->province_id = $geoData['provinceId'];
            $workshopsTable->save($workshop);
            $provinceName = $provincesMap[$workshop->province_id] ?? 'no province found';
            $io->out('Updating workshop: UID: ' . $workshop->uid . ' / Name: '. $workshop->name . ' / Province: ' . $provinceName . ' / Lat: '. $workshop->lat . ' / Lng: ' . $workshop->lng);
            usleep(300000);
        }

        $eventsTable = TableRegistry::getTableLocator()->get('Events');
        $events = $eventsTable->find('all')->where(
            [
                $eventsTable->aliasField('province_id') => 0,
                'DATE(Events.datumstart) >= DATE(NOW())',
            ]
        )->orderBy([
            $eventsTable->aliasField('uid') => 'ASC']
        );
    
        foreach($events as $event) {
            $geoData = $geoService->getGeoDataByCoordinates($event->lat, $event->lng);
            $event->province_id = $geoData['provinceId'];
            $eventsTable->save($event);
            $provinceName = $provincesMap[$event->province_id] ?? 'no province found';
            $io->out('Updating event: UID: ' . $event->uid . ' / Province: ' . $provinceName . ' / Lat: '. $event->lat . ' / Lng: ' . $event->lng);
            usleep(300000);
        }

        $usersTable = TableRegistry::getTableLocator()->get('Users');
        $users = $usersTable->find('all')->where(
            [
                $usersTable->aliasField('province_id') => 0,
                $usersTable->aliasField('lat IS NOT NULL'),
                $usersTable->aliasField('country_code IN') => ['DE', 'AT', 'CH'],
                
            ]
        )->orderBy([
            $usersTable->aliasField('uid') => 'ASC']
        );

        foreach($users as $user) {
            $user->revertPrivatizeData();
            $geoData = $geoService->getGeoDataByCoordinates($user->lat, $user->lng);
            $user->province_id = $geoData['provinceId'];
            $usersTable->save($user);
            $provinceName = $provincesMap[$user->province_id] ?? 'no province found';
            $io->out('Updating user: UID: ' . $user->uid . ' / Name: '. $user->firstname . ' ' . $user->lastname . ' / Province: ' . $provinceName . ' / Lat: '. $user->lat . ' / Lng: ' . $user->lng);
            usleep(300000);
        }


        return static::CODE_SUCCESS;

    }

}
