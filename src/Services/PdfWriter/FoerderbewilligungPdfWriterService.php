<?php
declare(strict_types=1);

namespace App\Services\PdfWriter;

use App\Services\Pdf\AppTcpdfService;
use App\Services\PdfWriter\PdfWriterService;

class FoerderbewilligungPdfWriterService extends PdfWriterService
{

    public function __construct()
    {
        $this->setPdfLibrary(new AppTcpdfService());
    }

}

