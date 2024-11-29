<?php
declare(strict_types=1);

namespace App\Services\PdfWriter;

use App\Services\Pdf\AppTcpdfService;
use App\Services\PdfWriter\PdfWriterService;
use App\Model\Entity\Fundingupload;
use Cake\Datasource\FactoryLocator;
use Cake\Core\Configure;
use Cake\I18n\DateTime;

class FoerderbewilligungPdfWriterService extends PdfWriterService
{

    public function __construct()
    {
        $this->setPdfLibrary(new AppTcpdfService());
    }

    public function prepareAndSetData($fundingUid)
    {

        $fundingsTable = FactoryLocator::get('Table')->get('Fundings');
        $funding = $fundingsTable->getUnprivatizedFundingWithAllAssociations($fundingUid);

        $now = DateTime::now();
        $filename = 'Foerderbewilligung_' . $funding->uid . '_' . $now->i18nFormat('yyyyMMdd_HHmmss') . '.pdf';
        $this->setFilename(Fundingupload::UPLOAD_PATH . $funding->uid . DS . 'attachments' . DS . $filename);
        
        $this->setData([
            'funding' => $funding,
            'formattedCurrentDate' => $now->i18nFormat(Configure::read('DateFormat.de.DateLong2')),
        ]);

    }

}

