<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class CreateCities extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('cities');

        $table
            ->addColumn('geonameid', 'integer', ['null' => false])
            ->addColumn('name', 'string', ['limit' => 200, 'null' => true])
            ->addColumn('asciiname', 'string', ['limit' => 200, 'null' => true])
            ->addColumn('alternatenames', 'text', ['null' => true])
            ->addColumn('latitude', 'double', ['null' => true])
            ->addColumn('longitude', 'double', ['null' => true])
            ->addColumn('feature_class', 'char', ['limit' => 1, 'null' => true])
            ->addColumn('feature_code', 'string', ['limit' => 10, 'null' => true])
            ->addColumn('country_code', 'char', ['limit' => 2, 'null' => true])
            ->addColumn('cc2', 'string', ['limit' => 60, 'null' => true])
            ->addColumn('admin1_code', 'string', ['limit' => 20, 'null' => true])
            ->addColumn('admin2_code', 'string', ['limit' => 80, 'null' => true])
            ->addColumn('admin3_code', 'string', ['limit' => 20, 'null' => true])
            ->addColumn('admin4_code', 'string', ['limit' => 20, 'null' => true])
            ->addColumn('population', 'biginteger', ['null' => true])
            ->addColumn('elevation', 'integer', ['null' => true])
            ->addColumn('dem', 'integer', ['null' => true])
            ->addColumn('timezone', 'string', ['limit' => 40, 'null' => true])
            ->addColumn('modification_date', 'date', ['null' => true])
            ->addIndex(['geonameid'], ['unique' => true])
            ->addIndex(['country_code'])
            ->addIndex(['latitude'])
            ->addIndex(['longitude'])
            ->addIndex(['name'])
            ->create();
    }
}
