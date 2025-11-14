<?php
declare(strict_types=1);
namespace App\Model\Table;

use Cake\ORM\Table;
use App\Model\Entity\City;
use Cake\ORM\Query\SelectQuery;
use Cake\Log\Log;

class CitiesTable extends Table
{
    public function findForFallback(string $keyword): ?City {
        $query = $this->find('all',
        conditions: [
            $this->aliasField('name') => $keyword,
        ],
        order: [
            'Cities.population' => 'DESC',
        ]);
        return $query->first();
    }


    public function getHaversineCondition(float $lat, float $lng, string $tableAlias): string
    {
        return "(6371 * acos(cos(radians($lat)) * cos(radians($tableAlias.lat)) * cos(radians($tableAlias.lng) - radians($lng)) + sin(radians($lat)) * sin(radians($tableAlias.lat))))";
    }
    /**
     * @param SelectQuery<\App\Model\Entity\Event|\App\Model\Entity\Workshop> $baseQuery
     * @param SelectQuery<\App\Model\Entity\Event|\App\Model\Entity\Workshop> $fallbackNearbyQuery
     * @return array{query: SelectQuery<\App\Model\Entity\Event|\App\Model\Entity\Workshop>, is_fallback: bool}
     */
    public function getFallbackNearbyQuery(SelectQuery $baseQuery, SelectQuery $fallbackNearbyQuery, string $keyword, string $tableAlias): array {

        if ($baseQuery->count() == 0 && $keyword != '') {
            $city = $this->findForFallback($keyword);
            if (!empty($city) && !empty($city->latitude) && !empty($city->longitude)) {
                $haversineCondition = $this->getHaversineCondition($city->latitude, $city->longitude, $tableAlias);
                $fallbackNearbyQuery->where(function ($exp) use ($haversineCondition) {
                    return $exp->lt($haversineCondition, City::FALLBACK_RADIUS_KM);
                });
                $fallbackNearbyCount = $fallbackNearbyQuery->count();
                if ($fallbackNearbyCount > 0) {
                    Log::error($fallbackNearbyCount . ' ' . $tableAlias . ' found near city "' . $keyword . '"');
                    return [
                        'query' => $fallbackNearbyQuery,
                        'is_fallback' => true,
                    ];
                }
            }
        }
        return [
            'query' => $baseQuery,
            'is_fallback' => false,
        ];

    }
}

?>