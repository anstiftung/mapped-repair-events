<?php
declare(strict_types=1);
namespace App\Model\Table;

use Cake\ORM\Table;
use App\Model\Entity\City;

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
}

?>