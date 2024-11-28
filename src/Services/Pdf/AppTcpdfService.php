<?php
namespace App\Services\Pdf;

use TCPDF;

class AppTcpdfService extends TCPDF
{

    public $fontSizeDefault = 11;
    public $fontSizeSmall = 8;

    public function __construct($orientation = 'P', $unit = 'mm', $format = 'A4', $unicode = true, $encoding = 'UTF-8', $diskcache = false, $pdfa = false)
    {
        parent::__construct($orientation, $unit, $format, $unicode, $encoding, $diskcache, $pdfa);
        mb_internal_encoding($encoding);
        $this->SetFont('freesans');
        $this->SetFontSize($this->fontSizeDefault);
        $this->SetCreator('anstiftung');
        $this->SetAuthor('anstiftung');
        $this->SetPrintHeader(true);
        $this->SetPrintFooter(false);
    }

}
