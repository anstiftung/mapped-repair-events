<?php
declare(strict_types=1);

use Cake\Datasource\FactoryLocator;
use Migrations\AbstractMigration;
use App\Model\Entity\Funding;

class Fundings7 extends AbstractMigration
{
    public function change(): void
    {

        $uploadTypes = [
            'activity_proof',
            'freistellungsbescheid',
            'zuwendungsbestaetigung',
        ];

        $fundingsTable = FactoryLocator::get('Table')->get('Fundings');
        $fundings = $fundingsTable->find()->contain([
            'FundinguploadsActivityProofs',
            'FundinguploadsFreistellungsbescheids',
            'FundinguploadsZuwendungsbestaetigungs',
        ]);

        foreach($uploadTypes as $uploadType) {
            $uploadEntity = 'fundinguploads_' . $uploadType . 's';
            $uploadStatusField = $uploadType . '_status';
            foreach($fundings as $funding) {
                $uploadsCount = count($funding->$uploadEntity);
                if ($uploadsCount == 0 &&  $funding->$uploadStatusField == Funding::STATUS_PENDING) {
                    $funding->$uploadStatusField = Funding::STATUS_UPLOAD_MISSING;
                    $fundingsTable->save($funding);
                }
            }
        }

        foreach($uploadTypes as $uploadType) {
            $uploadEntity = 'fundinguploads_' . $uploadType . 's';
            $uploadStatusField = $uploadType . '_status';
            $sql = "ALTER TABLE `fundings` CHANGE `".$uploadStatusField."` `".$uploadStatusField."` INT NULL DEFAULT '" . Funding::STATUS_UPLOAD_MISSING . "';";
            //$this->execute($sql);
        }

        $sql = "ALTER TABLE `fundings` DROP `status`;";
        //$this->execute($sql);

    }
}
