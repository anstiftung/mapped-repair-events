<?php
declare(strict_types=1);
namespace App\Model\Table;

/**
 * @extends \App\Model\Table\AppTable<\App\Model\Entity\Country>
 */
class CountriesTable extends AppTable
{

    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->setPrimaryKey('code');
    }

    /**
     * @return array<int, string>
     */
    public function getForDropdown(): array
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