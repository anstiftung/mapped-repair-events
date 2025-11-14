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

    public function getHaversineCondition(float $lat, float $lng, string $tableAlias): string
    {
        return "(6371 * acos(cos(radians($lat)) * cos(radians($tableAlias.lat)) * cos(radians($tableAlias.lng) - radians($lng)) + sin(radians($lat)) * sin(radians($tableAlias.lat))))";
    }

    public function getFallbackNearbyQuery(SelectQuery $baseQuery, SelectQuery $fallbackNearbyQuery, string $keyword, string $tableAlias): SelectQuery {

        if ($baseQuery->count() == 0 && $keyword != '') {
            $citiesTable = FactoryLocator::get('Table')->get('Cities');
            $city = $citiesTable->findForFallback($keyword);
            if (!empty($city) && !empty($city->latitude) && !empty($city->longitude)) {
                $haversineCondition = $this->getHaversineCondition($city->latitude, $city->longitude, $tableAlias);
                $fallbackNearbyQuery->where(function ($exp) use ($haversineCondition) {
                    return $exp->lt($haversineCondition, Event::FALLBACK_RADIUS_KM);
                });
                $fallbackNearbyCount = $fallbackNearbyQuery->count();
                if ($fallbackNearbyCount > 0) {
                    $fallbackNearbyQuery->is_fallback = true; // phpstan-ignore-line
                    Log::error($fallbackNearbyCount . ' ' . $tableAlias . ' found near city "' . $keyword . '"');
                    return $fallbackNearbyQuery;
                }
            }
        }
        return $baseQuery;


    }

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
            $provincesTable = FactoryLocator::get('Table')->get('Provinces');
            foreach($output->results[0]->address_components as $addressComponent) {
                if ($addressComponent->types[0] == 'administrative_area_level_1') {
                    // @phpstan-ignore-next-line
                    $province = $provincesTable->find()->where([
                        'OR' =>
                            [
                                $provincesTable->aliasField('name') => $addressComponent->long_name,
                                'FIND_IN_SET(:long_name, alternative_names) !=' => 0,
                            ],
                    ])
                    ->bind(':long_name', $addressComponent->long_name, 'string')
                    ->first();
                    continue;
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