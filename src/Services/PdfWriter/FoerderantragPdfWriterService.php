<?php
declare(strict_types=1);

namespace App\Services\PdfWriter;

use App\Model\Entity\Funding;
use App\Services\Pdf\AppTcpdfService;
use App\Services\PdfWriter\PdfWriterService;
use App\Model\Entity\Fundingupload;
use Cake\Datasource\FactoryLocator;
use Cake\Core\Configure;
use Cake\I18n\DateTime;

class FoerderantragPdfWriterService extends PdfWriterService
{

    public function __construct()
    {
        $this->setPdfLibrary(new AppTcpdfService());
    }

    public function prepareAndSetData($fundingUid, $timestamp)
    {

        $fundingsTable = FactoryLocator::get('Table')->get('Fundings');
        $funding = $fundingsTable->getUnprivatizedFundingWithAllAssociations($fundingUid);

        $filename = 'Foerderantrag_' . $funding->uid . '_' . $timestamp->i18nFormat('yyyyMMdd_HHmmss') . '.pdf';
        $this->setFilename(Fundingupload::UPLOAD_PATH . $funding->uid . DS . 'attachments' . DS . $filename);
        
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
            'formattedTimestamp' => $timestamp->i18nFormat(Configure::read('DateFormat.de.DateLong2')),
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

