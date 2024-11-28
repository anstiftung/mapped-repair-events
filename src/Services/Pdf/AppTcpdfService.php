<?php
namespace App\Services\Pdf;

use TCPDF;

class AppTcpdfService extends TCPDF
{
    public function __construct($orientation = 'P', $unit = 'mm', $format = 'A4', $unicode = true, $encoding = 'UTF-8', $diskcache = false, $pdfa = false)
    {
        parent::__construct($orientation, $unit, $format, $unicode, $encoding, $diskcache, $pdfa);
        mb_internal_encoding('UTF-8');
        $this->FontFamily = 'freesans';
        $this->SetCreator('anstiftung');
        $this->SetAuthor('anstiftung');

    }

}
