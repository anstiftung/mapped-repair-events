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

    public function initialize(array $config): void {
        parent::initialize($config);
        $this->belongsTo('Workshops', [
            'foreignKey' => 'workshop_uid',
        ]);
        $this->belongsTo('Supporters', [
            'foreignKey' => 'supporter_id',
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
        ])->contain(['Supporters'])->first();
        if (empty($funding)) {
            throw new NotFoundException('funding (UID: '.$fundingUid.') was not found');
        }
        $this->delete($funding);
        $supportersTable = FactoryLocator::get('Table')->get('Supporters');
        $supportersTable->delete($funding->supporter);

        // fundinguploads are deleted automatically by dependent option

        $filePath = Funding::UPLOAD_PATH . $funding->uid;
        FolderService::deleteFolder($filePath);

    }

    public function findOrCreateCustom($workshopUid) {

        $funding = $this->find()->where([
            $this->aliasField('workshop_uid') => $workshopUid,
            $this->aliasField('owner') => Router::getRequest()?->getAttribute('identity')?->uid,
        ])->first();

        if (empty($funding)) {
            $supportersTable = FactoryLocator::get('Table')->get('Supporters');
            $supporterEntity = $supportersTable->newEmptyEntity();
            $supporterEntity->name = '';
            $supporter = $supportersTable->save($supporterEntity);
            $associations = ['Supporters'];
            $newEntity = $this->newEntity([
                'workshop_uid' => $workshopUid,
                'status' => APP_ON,
                'owner' => Router::getRequest()?->getAttribute('identity')?->uid,
                'supporter_id' => $supporter->id,
            ]);
            $funding = $this->save($newEntity, ['associated' => $associations]);
        }

        $funding = $this->find()->where([
            $this->aliasField('uid') => $funding->uid,
            $this->aliasField('owner') => Router::getRequest()?->getAttribute('identity')?->uid,
        ])->contain([
            'Workshops.Countries',
            'OwnerUsers.Countries',
            'Supporters',
            'Fundinguploads',
        ])->first();

        if (!empty($funding)) {
            $funding->owner_user->revertPrivatizeData();
        }
        return $funding;
    }

}

?>