<?php
declare(strict_types=1);

namespace App\Services;

use Cake\Core\Configure;
use Cake\Datasource\FactoryLocator;
use Cake\Validation\Validator;
use Cake\Log\Log;
use Cake\ORM\Query\SelectQuery;
use App\Model\Entity\Event;

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

    public function isPointInBoundingBox(float $lat, float $lng): bool
    {
        return $lat >= self::VALID_BOUNDING_BOX['lat']['min'] && $lat <= self::VALID_BOUNDING_BOX['lat']['max'] && $lng >= self::VALID_BOUNDING_BOX['lng']['min'] && $lng <= self::VALID_BOUNDING_BOX['lng']['max'];
    }

    /**
     * @return array<string, int>
     */
    public function getGeoDataByCoordinates(string $lat, string $lng): array
    {
        $requestUrl = 'https://maps.googleapis.com/maps/api/geocode/json?key='.Configure::read('googleMapApiKey').'&latlng=' . $lat . ',' . $lng;
        $output = $this->getDecodedOutput($requestUrl);
        $provinceId = $this->getProvinceIdByGeocodeResult($output);
        return ['provinceId' => $provinceId];
    }

    /**
     * @return array<string, string|int>
     */
    public function getGeoDataByAddress(string $addressString): array
    {

        $lat = 'ungültig';
        $lng = 'ungültig';

        $addressString = Configure::read('AppConfig.htmlHelper')->replaceAddressAbbreviations($addressString);
        $addressString = trim((string) $addressString);
        $requestUrl = 'https://maps.googleapis.com/maps/api/geocode/json?key='.Configure::read('googleMapApiKey').'&address=' . urlencode($addressString);
        $output = $this->getDecodedOutput($requestUrl);

        if ($output->status == 'OK') {
            $lat = str_replace(',', '.', (string) $output->results[0]->geometry->location->lat);
            $lng = str_replace(',', '.', (string) $output->results[0]->geometry->location->lng);
        }

        $provinceId = $this->getProvinceIdByGeocodeResult($output);

        return ['lat' => $lat, 'lng' => $lng, 'provinceId' => $provinceId];
    }

    private function getDecodedOutput(string $requestUrl): object
    {
        $geocode = file_get_contents($requestUrl);
        $output = json_decode($geocode);
        return $output;
    }

    private function getProvinceIdByGeocodeResult(object $output): int
    {

        $provinceId = 0;

        if (!empty($output->results) && isset($output->results[0]->address_components)) {
            /** @var \App\Model\Table\ProvincesTable $provincesTable */
            $provincesTable = FactoryLocator::get('Table')->get('Provinces');
            $province = null;
            foreach($output->results[0]->address_components as $addressComponent) {
                if ($addressComponent->types[0] == 'administrative_area_level_1') {
                    $province = $provincesTable->findByName($addressComponent->long_name);
                    break;
                }
            }
        }

        if (!empty($province)) {
            $provinceId = $province->id;
        }

        return $provinceId;
    }

    public function getGeoCoordinatesValidator(Validator $validator): Validator
    {
        $geoFields = ['lat', 'lng'];
        foreach($geoFields as $geoField) {
            $validator->add($geoField, 'geoCoordinatesInBoundingBox', [
                'rule' => function ($value, array $context): bool {
                    if ($context['data']['use_custom_coordinates']) {
                        if (!$this->isPointInBoundingBox((float) $context['data']['lat'], (float) $context['data']['lng'])) {
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