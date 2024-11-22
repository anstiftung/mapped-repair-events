<?php
namespace App\Model\Table;

use AssetCompress\Factory;
use Cake\Routing\Router;
use Cake\Database\Schema\TableSchemaInterface;
use Cake\Datasource\FactoryLocator;
use App\Model\Entity\Funding;
use App\Services\FolderService;
use Cake\Http\Exception\NotFoundException;
use Cake\Validation\Validator;
use Laminas\Diactoros\UploadedFile;

class FundingsTable extends AppTable
{

    const FUNDINGBUDGETPLANS_COUNT_VISIBLE = 5;
    const FUNDINGBUDGETPLANS_COUNT_HIDDEN = 10;
    const FUNDINGBUDGETPLANS_COUNT = self::FUNDINGBUDGETPLANS_COUNT_VISIBLE + self::FUNDINGBUDGETPLANS_COUNT_HIDDEN;

    public function initialize(array $config): void {
        parent::initialize($config);
        $this->belongsTo('Workshops', [
            'foreignKey' => 'workshop_uid',
        ]);
        $this->belongsTo('Fundingdatas', [
            'foreignKey' => 'fundingdata_id',
        ]);
        $this->belongsTo('Fundingsupporters', [
            'foreignKey' => 'fundingsupporter_id',
        ]);
        $this->hasMany('Fundingbudgetplans', [
            'foreignKey' => 'funding_uid',
            'dependent' => true,
        ]);
        $this->hasMany('Fundinguploads', [
            'foreignKey' => 'funding_uid',
            'dependent' => true,
        ]);
    }

    public function getSchema(): TableSchemaInterface
    {
        return parent::getSchema()->setColumnType('verified_fields', 'json');
    }

    public function validationDefault(Validator $validator): Validator
    {

        $validator->add('fundinguploads', 'fileCount', [
            'rule' => function ($value, $context) {
                if (count($value) > 5) {
                    return 'Insgesamt sind maximal 5 Dateien erlaubt.';
                }
                return true;
            },
        ]);

        $validator->add('files_fundinguploads', 'fileTypeAndSize', [
            'rule' => function ($value, $context) {
                $allowedMimeTypes = ['application/pdf', 'image/jpeg', 'image/png'];
                $maxSize = 5 * 1024 * 1024; // 5 MB in bytes

                $errorMessages = [];
                foreach ($context['data']['files_fundinguploads'] as $file) {
                    if (!$file instanceof UploadedFile) {
                        continue;
                    }
                    if ($file->getError() !== UPLOAD_ERR_OK) {
                        continue;
                    }

                    if ($file->getSize() > $maxSize) {
                        $errorMessages[] = $file->getClientFilename() . ': Datei muss weniger als 5 MB groß sein.';
                    }

                    if (!in_array($file->getClientMediaType(), $allowedMimeTypes)) {
                        $errorMessages[] = $file->getClientFilename() . ': Nur PDF, JPG und PNG-Dateien sind erlaubt.';
                    }

                }

                if (!empty($errorMessages)) {
                    return implode(' / ', $errorMessages);
                }
                return true;
            },
        ]);
        return $validator;
    }

    public function deleteCustom($fundingUid) {

        $funding = $this->find()->where([
            $this->aliasField('uid') => $fundingUid,
        ])->contain(['Fundingsupporters', 'Fundingdatas'])->first();
        if (empty($funding)) {
            throw new NotFoundException('funding (UID: '.$fundingUid.') was not found');
        }
        $this->delete($funding);

        $fundingsupportersTable = FactoryLocator::get('Table')->get('Fundingsupporters');
        $fundingsupportersTable->delete($funding->fundingsupporter);

        $fundingdatasTable = FactoryLocator::get('Table')->get('Fundingdatas');
        $fundingdatasTable->delete($funding->fundingdata);

        // fundinguploads and fundingbudgetplans are deleted automatically by dependent option

        $filePath = Funding::UPLOAD_PATH . $funding->uid;
        FolderService::deleteFolder($filePath);

    }

    public function findOrCreateCustom($workshopUid) {

        $funding = $this->find()->where([
            $this->aliasField('workshop_uid') => $workshopUid,
            $this->aliasField('owner') => Router::getRequest()?->getAttribute('identity')?->uid,
        ])->first();

        if (empty($funding)) {

            $fundingsupportersTable = FactoryLocator::get('Table')->get('Fundingsupporters');
            $fundingsupporterEntity = $fundingsupportersTable->newEmptyEntity();
            $fundingsupporterEntity->name = '';
            $fundingsupporterEntity = $fundingsupportersTable->save($fundingsupporterEntity);
            
            $fundingdatasTable = FactoryLocator::get('Table')->get('Fundingdatas');
            $fundingdataEntity = $fundingdatasTable->newEmptyEntity();
            $fundingdataEntity->description = '';
            $fundingdataEntity = $fundingdatasTable->save($fundingdataEntity);

            $associations = ['Fundingsupporters', 'Fundingdatas'];
            $newEntity = $this->newEntity([
                'workshop_uid' => $workshopUid,
                'status' => APP_ON,
                'owner' => Router::getRequest()?->getAttribute('identity')?->uid,
                'fundingdata_id' => $fundingdataEntity->id,
                'fundingsupporter_id' => $fundingsupporterEntity->id,
            ]);
            $funding = $this->save($newEntity, ['associated' => $associations]);
        }

        $workshopsTable = FactoryLocator::get('Table')->get('Workshops');
        $funding = $this->find()->where([
            $this->aliasField('uid') => $funding->uid,
            $this->aliasField('owner') => Router::getRequest()?->getAttribute('identity')?->uid,
        ])->contain([
            'Workshops' => $workshopsTable->getFundingContain(),
            'OwnerUsers',
            'Fundingbudgetplans',
            'Fundingdatas',
            'Fundingsupporters',
            'Fundinguploads',
        ])->first();

        if (empty($funding->fundingbudgetplans)) {
            $fundingbudgetplansTable = FactoryLocator::get('Table')->get('Fundingbudgetplans');
            $fundingbudgetplanEntities = [];
            for ($i = 0; $i < self::FUNDINGBUDGETPLANS_COUNT; $i++) {
                $fundingbudgetplanEntity = $fundingbudgetplansTable->newEmptyEntity();
                $fundingbudgetplanEntity->funding_uid = $funding->uid;
                $fundingbudgetplanEntity = $fundingbudgetplansTable->save($fundingbudgetplanEntity, ['validate' => false]);
                $fundingbudgetplanEntities[] = $fundingbudgetplanEntity;
            }
            $funding->fundingbudgetplans = $fundingbudgetplanEntities;
        }

        if (!empty($funding)) {
            $funding->owner_user->revertPrivatizeData();
        }
        return $funding;
    }

}

?>