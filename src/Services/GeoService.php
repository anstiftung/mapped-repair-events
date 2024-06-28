<?php

namespace App\Services;

use Cake\Core\Configure;
use Cake\Datasource\FactoryLocator;
use Cake\Http\Exception\ServiceUnavailableException;
use Cake\Validation\Validator;
use Cake\Log\Log;

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

    const ERROR_OUT_OF_BOUNDING_BOX = 'Die Geo-Koordinaten liegen nicht in Europa, vielleicht hast du Breite (Lat) und Länge (Long) vertauscht?';

    public function isPointInBoundingBox($lat, $lng) {
        return $lat >= self::VALID_BOUNDING_BOX['lat']['min'] && $lat <= self::VALID_BOUNDING_BOX['lat']['max'] && $lng >= self::VALID_BOUNDING_BOX['lng']['min'] && $lng <= self::VALID_BOUNDING_BOX['lng']['max'];
    }

    public function getGeoData($addressString) {

        if (Configure::read('googleMapApiKey') == '') {
            throw new ServiceUnavailableException('googleMapApiKey not defined');
        }

        $lat = 'ungültig';
        $lng = 'ungültig';

        $addressString = Configure::read('AppConfig.htmlHelper')->replaceAddressAbbreviations($addressString);
        $addressString = trim($addressString);
        $geocode = file_get_contents('https://maps.googleapis.com/maps/api/geocode/json?key='.Configure::read('googleMapApiKey').'&address=' . urlencode($addressString));
        $output = json_decode($geocode);

        $provincesTable = FactoryLocator::get('Table')->get('Provinces');
        foreach($output->results[0]->address_components as $addressComponent) {
            if ($addressComponent->types[0] == 'administrative_area_level_1') {
                $province = $provincesTable->find()->where([
                    'OR' => 
                        [
                            $provincesTable->aliasField('name') => $addressComponent->long_name,
                            $provincesTable->aliasField('alternative_names LIKE') => '%' . $addressComponent->long_name . '%',
                        ],
                ])->first();
                if (empty($province)) {
                    Log::error('Province not found: ' . $addressComponent->long_name);
                }
                continue;
            }
        }

        $provinceId = 0;
        if (!empty($province)) {
            $provinceId = $province->id;
        }

        if ($output->status == 'OK') {
            $lat = str_replace(',', '.', $output->results[0]->geometry->location->lat);
            $lng = str_replace(',', '.', $output->results[0]->geometry->location->lng);
        }

        return ['lat' => $lat, 'lng' => $lng, 'provinceId' => $provinceId];
    }

    public function getGeoCoordinatesValidator(Validator $validator)
    {
        $geoFields = ['lat', 'lng'];
        foreach($geoFields as $geoField) {
            $validator->add($geoField, 'geoCoordinatesInBoundingBox', [
                'rule' => function ($value, $context) {
                    if ($context['data']['use_custom_coordinates']) {
                        if (!$this->isPointInBoundingBox($context['data']['lat'], $context['data']['lng'])) {
                            Log::error('Geo coordinates out of bounding box: lat: ' . json_encode($context['data']['lat']) . ' / lng: ' . json_encode($context['data']['lng']));
                            return false;
                        }
                    }
                    return true;
                },
                'message' => self::ERROR_OUT_OF_BOUNDING_BOX,
            ]);
        }
        return $validator;
    }

}