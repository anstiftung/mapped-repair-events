<?php
declare(strict_types=1);

namespace App\Services\PdfWriter;

use App\Services\Pdf\AppTcpdfService;
use App\Services\PdfWriter\PdfWriterService;
use App\Model\Entity\Fundingupload;
use Cake\Datasource\FactoryLocator;

class FoerderbewilligungPdfWriterService extends PdfWriterService
{

    public function __construct()
    {
        $this->setPdfLibrary(new AppTcpdfService());
    }

    public function prepareAndSetData($fundingUid, $timestamp)
    {

        $fundingsTable = FactoryLocator::get('Table')->get('Fundings');
        $funding = $fundingsTable->getUnprivatizedFundingWithAllAssociations($fundingUid);

        $filename = 'Foerderbewilligung_anstiftung_bmuv_' . $funding->uid . '_' . $timestamp->i18nFormat('yyyyMMdd_HHmmss') . '.pdf';
        $this->setFilename(Fundingupload::UPLOAD_PATH . $funding->uid . DS . 'attachments' . DS . $filename);
        
        $this->setData([
            'funding' => $funding,
            'timestamp' => $timestamp,
        ]);

    }

}

