<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class ProvincesData extends BaseMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     * @return void
     */
    public function change(): void
    {

        $data = [
            ['name' => 'Baden-Württemberg', 'country_code' => 'DE', 'alternative_names' => 'Baden-Wuerttemberg'],
            ['name' => 'Bayern', 'country_code' => 'DE', 'alternative_names' => 'Bavaria'],
            ['name' => 'Berlin', 'country_code' => 'DE'],
            ['name' => 'Brandenburg', 'country_code' => 'DE'],
            ['name' => 'Bremen', 'country_code' => 'DE'],
            ['name' => 'Hamburg', 'country_code' => 'DE'],
            ['name' => 'Hessen', 'country_code' => 'DE', 'alternative_names' => 'Hesse'],
            ['name' => 'Mecklenburg-Vorpommern', 'country_code' => 'DE'],
            ['name' => 'Niedersachsen', 'country_code' => 'DE', 'alternative_names' => 'Lower Saxony'],
            ['name' => 'Nordrhein-Westfalen', 'country_code' => 'DE', 'alternative_names' => 'North Rhine-Westphalia'],
            ['name' => 'Rheinland-Pfalz', 'country_code' => 'DE', 'alternative_names' => 'Rhineland-Palatinate'],
            ['name' => 'Saarland', 'country_code' => 'DE'],
            ['name' => 'Sachsen', 'country_code' => 'DE', 'alternative_names' => 'Saxony'],
            ['name' => 'Sachsen-Anhalt', 'country_code' => 'DE', 'alternative_names' => 'Saxony-Anhalt'],
            ['name' => 'Schleswig-Holstein', 'country_code' => 'DE'],
            ['name' => 'Thüringen', 'country_code' => 'DE', 'alternative_names' => 'Thuringia'],
        ];
        $this->table('provinces')->insert($data)->save();

        $data = [
            ['name' => 'Burgenland', 'country_code' => 'AT'],
            ['name' => 'Kärnten', 'country_code' => 'AT', 'alternative_names' => 'Carinthia'],
            ['name' => 'Niederösterreich', 'country_code' => 'AT', 'alternative_names' => 'Lower Austria'],
            ['name' => 'Oberösterreich', 'country_code' => 'AT', 'alternative_names' => 'Upper Austria'],
            ['name' => 'Salzburg', 'country_code' => 'AT'],
            ['name' => 'Steiermark', 'country_code' => 'AT', 'alternative_names' => 'Styria'],
            ['name' => 'Tirol', 'country_code' => 'AT', 'alternative_names' => 'Tyrol'],
            ['name' => 'Vorarlberg', 'country_code' => 'AT'],
            ['name' => 'Wien', 'country_code' => 'AT', 'alternative_names' => 'Vienna'],
        ];
        $this->table('provinces')->insert($data)->save();

        $data = [
            ['name' => 'Aargau', 'country_code' => 'CH', 'alternative_names' => 'Argovia'],
            ['name' => 'Appenzell Ausserrhoden', 'country_code' => 'CH', 'alternative_names' => 'Outer Rhodes'],
            ['name' => 'Appenzell Innerrhoden', 'country_code' => 'CH', 'alternative_names' => 'Inner Rhodes'],
            ['name' => 'Basel-Landschaft', 'country_code' => 'CH', 'alternative_names' => 'Basel-Country'],
            ['name' => 'Basel-Stadt', 'country_code' => 'CH', 'alternative_names' => 'Basel-City'],
            ['name' => 'Bern', 'country_code' => 'CH'],
            ['name' => 'Freiburg', 'country_code' => 'CH', 'alternative_names' => 'Fribourg'],
            ['name' => 'Genf', 'country_code' => 'CH', 'alternative_names' => 'Geneva'],
            ['name' => 'Glarus', 'country_code' => 'CH'],
            ['name' => 'Graubünden', 'country_code' => 'CH', 'alternative_names' => 'Grisons'],
            ['name' => 'Jura', 'country_code' => 'CH'],
            ['name' => 'Luzern', 'country_code' => 'CH', 'alternative_names' => 'Lucerne'],
            ['name' => 'Neuenburg', 'country_code' => 'CH', 'alternative_names' => 'Neuchâtel'],
            ['name' => 'Nidwalden', 'country_code' => 'CH', 'alternative_names' => 'Nidwald'],
            ['name' => 'Obwalden', 'country_code' => 'CH', 'alternative_names' => 'Obwald'],
            ['name' => 'Schaffhausen', 'country_code' => 'CH'],
            ['name' => 'Schwyz', 'country_code' => 'CH'],
            ['name' => 'Solothurn', 'country_code' => 'CH'],
            ['name' => 'St. Gallen', 'country_code' => 'CH', 'alternative_names' => 'St. Gall,Sankt Gallen'],
            ['name' => 'Tessin', 'country_code' => 'CH', 'alternative_names' => 'Ticino'],
            ['name' => 'Thurgau', 'country_code' => 'CH', 'alternative_names' => 'Thurgovia'],
            ['name' => 'Uri', 'country_code' => 'CH'],
            ['name' => 'Waadt', 'country_code' => 'CH', 'alternative_names' => 'Vaud'],
            ['name' => 'Wallis', 'country_code' => 'CH', 'alternative_names' => 'Valais'],
            ['name' => 'Zug', 'country_code' => 'CH'],
            ['name' => 'Zürich', 'country_code' => 'CH', 'alternative_names' => 'Zurich'],
        ];

        $this->table('provinces')->insert($data)->save();


    }
}
