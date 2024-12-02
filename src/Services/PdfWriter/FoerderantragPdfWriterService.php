<?php
declare(strict_types=1);

namespace App\Services\PdfWriter;

use App\Model\Entity\Funding;
use App\Services\PdfWriter\PdfWriterService;
use App\Model\Entity\Fundingupload;
use Cake\Datasource\FactoryLocator;
use App\Services\Pdf\FoerderantragTcpdfService;

class FoerderantragPdfWriterService extends PdfWriterService
{

    public function __construct()
    {
        $this->setPdfLibrary(new FoerderantragTcpdfService());
    }

    public function getFilenameCustom($funding, $timestamp) {
        return 'Foerderantrag_anstiftung_bmuv_' . $funding->uid . '_' . $timestamp->i18nFormat('yyyyMMdd_HHmmss') . '.pdf';
    }

    public function getUploadPath($fundingUid) {
        return Fundingupload::UPLOAD_PATH . $fundingUid . DS . 'attachments' . DS;
    }

    public function prepareAndSetData($fundingUid, $timestamp)
    {

        $fundingsTable = FactoryLocator::get('Table')->get('Fundings');
        $funding = $fundingsTable->getUnprivatizedFundingWithAllAssociations($fundingUid);

        $filename = $this->getFilenameCustom($funding, $timestamp);
        $this->setFilename($this->getUploadPath($funding->uid) . $filename);
        
        $blocks = [
            [
                'name' => Funding::FIELDS_WORKSHOP_LABEL,
                'fields' => $this->getPreparedFields(Funding::FIELDS_WORKSHOP, $funding->workshop),
            ],
            [
                'name' => Funding::FIELDS_OWNER_USER_LABEL,
                'fields' => $this->getPreparedFields(Funding::FIELDS_OWNER_USER, $funding->owner_user),
            ],
            [
                'name' => Funding::FIELDS_FUNDINGSUPPORTER_ORGANIZATION_LABEL,
                'fields' => $this->getPreparedFields(Funding::FIELDS_FUNDINGSUPPORTER_ORGANIZATION, $funding->fundingsupporter),
            ],
            [
                'name' => Funding::FIELDS_FUNDINGSUPPORTER_USER_LABEL,
                'fields' => $this->getPreparedFields(Funding::FIELDS_FUNDINGSUPPORTER_USER, $funding->fundingsupporter),
            ],
            [
                'name' => Funding::FIELDS_FUNDINGSUPPORTER_BANK_LABEL,
                'fields' => $this->getPreparedFields(Funding::FIELDS_FUNDINGSUPPORTER_BANK, $funding->fundingsupporter),
            ],
        ];
        
        $this->setData([
            'blocks' => $blocks,
            'description' => $this->getPreparedFields(Funding::FIELDS_FUNDINGDATA_DESCRIPTION, $funding->fundingdata),
            'checkboxes' => $this->getPreparedFields(Funding::FIELDS_FUNDING_DATA_CHECKBOXES, $funding->fundingdata),
            'timestamp' => $timestamp,
            'funding' => $funding,
        ]);

    }

    private function getPreparedFields($definedFields, $entity) {
        $preparedFields = [];
        foreach($definedFields as $workshopField) {
            $workshopFieldName = $workshopField['name'];
            $preparedFields[$workshopFieldName] = [
                'name' => $workshopFieldName,
                'label' => $workshopField['options']['label'],
                'value' => $entity[$workshopFieldName],
            ];
        }
        return $preparedFields;
    }

}

