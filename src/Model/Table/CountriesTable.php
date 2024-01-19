<?php
namespace App\Model\Table;

use Cake\ORM\Table;

class CountriesTable extends Table
{

    public $primaryKey = 'code';

    public function getForDropdown()
    {
        $countries = $this->find('all', order: [
            'rank' => 'ASC',
            'name_de' => 'ASC'
        ]);

        $preparedCountries = [];
        foreach($countries as $country) {
            $preparedCountries[$country->code] = $country->name_de;
        }

        return $preparedCountries;
    }
}

?>