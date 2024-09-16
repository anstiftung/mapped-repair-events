<?php

namespace App\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use League\Csv\Reader;
use Cake\Datasource\FactoryLocator;

class UpdateCfAndMfCommand extends Command
{

    public function execute(Arguments $args, ConsoleIo $io)
    {

        $categoriesTable = FactoryLocator::get('Table')->get('Categories');
        $categories = $categoriesTable->find('all')->contain(['ParentCategories'])->toArray();

        $reader = Reader::createFromPath(CONFIG . 'sql' . DS . 'cf-mf-update-2024-09.csv');
        $csvRecords = $reader->getRecords();

        $i = 0;
        foreach($categories as $category) {

            foreach($csvRecords as $csvRecord) {
                
                $csvRecordParentCategory = $csvRecord[0];
                $csvRecordCategory = $csvRecord[1];

                if (
                    ($category->parent_category && $category->parent_category->name == $csvRecordParentCategory) && 
                     $category->name == $csvRecordCategory) {
                    $category->carbon_footprint = (float) str_replace(',', '.', $csvRecord[2]);
                    $category->material_footprint = (float) str_replace(',', '.', $csvRecord[3]);
                    $categoriesTable->save($category);
                    $i++;
                    $io->out($i . ' updating category: ' . $category->name . ' / CF: ' . $category->carbon_footprint . ' / MF: ' . $category->material_footprint);
                    continue;
                }

            }

        }


        return static::CODE_SUCCESS;

    }

}
