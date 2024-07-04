<?php

namespace App\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Datasource\FactoryLocator;
use App\Services\GeoService;

class UpdateProvicesFromGeoDataCommand extends Command
{

    public function execute(Arguments $args, ConsoleIo $io)
    {

        $geoService = new GeoService();
        
        $workshopsTable = FactoryLocator::get('Table')->get('Workshops');
        $provincesTable = FactoryLocator::get('Table')->get('Provinces');
        $provincesMap = $provincesTable->find('list', keyField: 'id', valueField: 'name')->toArray();

        $workshops = $workshopsTable->find('all')->where(
            [
                $workshopsTable->aliasField('province_id') => 0,
            ]
        )->orderAsc($workshopsTable->aliasField('uid'));

        foreach($workshops as $workshop) {
            $geoData = $geoService->getGeoDataByCoordinates($workshop->lat, $workshop->lng);
            $workshop->province_id = $geoData['provinceId'];
            $workshopsTable->save($workshop);
            $provinceName = $provincesMap[$workshop->province_id] ?? 'no province found';
            $io->out('Updating workshop: UID: ' . $workshop->uid . ' / Name: '. $workshop->name . ' / Province: ' . $provinceName . ' / Lat: '. $workshop->lat . ' / Lng: ' . $workshop->lng);
            usleep(300000);
        }

        return static::CODE_SUCCESS;

    }

}
