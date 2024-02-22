<?php

namespace App\Services;

use Cake\Core\Configure;
use Cake\Http\Exception\ServiceUnavailableException;

class GeoService {

    const VALID_BOUNDING_BOX = [
        'lng' => [
            'max' => 61.230465,
            'min' => -25.502931
        ],
        'lat' => [
            'max' => 68.697848,
            'min' => 29.751635
        ],
    ];

    public function isPointInBoundingBox($lat, $lng) {
        return $lat >= self::VALID_BOUNDING_BOX['lat']['min'] && $lat <= self::VALID_BOUNDING_BOX['lat']['max'] && $lng >= self::VALID_BOUNDING_BOX['lng']['min'] && $lng <= self::VALID_BOUNDING_BOX['lng']['max'];
    }

    public function getLatLngFromGeoCodingService($addressString) {

        if (Configure::read('googleMapApiKey') == '') {
            throw new ServiceUnavailableException('googleMapApiKey not defined');
        }

        $lat = 'ungültig';
        $lng = 'ungültig';

        $addressString = Configure::read('AppConfig.htmlHelper')->replaceAddressAbbreviations($addressString);
        $addressString = trim($addressString);
        $geocode = file_get_contents('https://maps.googleapis.com/maps/api/geocode/json?key='.Configure::read('googleMapApiKey').'&address=' . urlencode($addressString));
        $output = json_decode($geocode);

        if ($output->status == 'OK') {
            $lat = str_replace(',', '.', $output->results[0]->geometry->location->lat);
            $lng = str_replace(',', '.', $output->results[0]->geometry->location->lng);
        }

        return ['lat' => $lat, 'lng' => $lng];
    }

}