<?php
declare(strict_types=1);

use Cake\Datasource\FactoryLocator;
use Migrations\AbstractMigration;
use App\Model\Entity\Funding;

class Fundings7 extends AbstractMigration
{
    public function change(): void
    {

        $sql = "ALTER TABLE `fundings` DROP `status`;";
        $this->execute($sql);

        $uploadTypes = [
            'activity_proof',
            'freistellungsbescheid',
            'zuwendungsbestaetigung',
        ];

        foreach($uploadTypes as $uploadType) {
            $uploadStatusField = $uploadType . '_status';
            $sql = "ALTER TABLE `fundings` CHANGE `".$uploadStatusField."` `".$uploadStatusField."` INT NULL DEFAULT '" . Funding::STATUS_UPLOAD_MISSING . "';";
            $this->execute($sql);
        }

    }
}
