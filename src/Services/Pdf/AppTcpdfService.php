<?php
declare(strict_types=1);
namespace App\Services\Pdf;

use TCPDF;

abstract class AppTcpdfService extends TCPDF
{

    public $backgroundImageFile = '';
    public $fontSizeBig = 12;
    public $fontSizeDefault = 10;
    public $fontSizeSmall = 8;

    public function __construct($orientation = 'P', $unit = 'mm', $format = 'A4', $unicode = true, $encoding = 'UTF-8', $diskcache = false, $pdfa = false)
    {
        parent::__construct($orientation, $unit, $format, $unicode, $encoding, $diskcache, $pdfa);
        mb_internal_encoding($encoding);
        $this->SetFont('verdana');
        $this->SetFontSize($this->fontSizeDefault);
        $this->SetCreator('anstiftung');
        $this->SetAuthor('anstiftung');
        $this->SetPrintHeader(true);
        $this->SetPrintFooter(false);
    }

    public function SetFontSizeBig()
    {
        $this->SetFontSize($this->fontSizeBig);
    }

    public function SetFontSizeDefault()
    {
        $this->SetFontSize($this->fontSizeDefault);
    }

    public function SetFontSizeSmall()
    {
        $this->SetFontSize($this->fontSizeSmall);
    }

    public function setDefaults()
    {

        $this->setPrintFooter(false);

        $this->AddPage();

        $this->SetLeftMargin(18);
        $this->SetRightMargin(18);

        $this->SetAutoPageBreak(true, 0);

    }

    public function Header() {
        $this->SetMargins(0, 0, 0);
        $this->SetAutoPageBreak(false, 0);

        $pageWidth = $this->getPageWidth();
        $pageHeight = $this->getPageHeight();

        $this->Image($this->backgroundImageFile, 0, 0, $pageWidth, $pageHeight);
    }

    public function getFundingDataAsTable($data, $width = '100%', $widthColumnA = '30%', $widthColumnB = '70%', $alignmentColumnA = 'left', $alignmentColumnB = 'left')
    {
        $html = '<table width="' . $width . '" border="0" cellpadding="0">';
            foreach($data as $value) {
                $html .= '<tr>';
                    $html .= '<td width="'.$widthColumnA.'" align="' . $alignmentColumnA . '">';
                        $html .= $value['label'];
                    $html .= '</td>';
                    $html .= '<td width="'.$widthColumnB.'" align="' . $alignmentColumnB . '">';
                        $html .= $value['value'];
                    $html .= '</td>';
                $html .= '</tr>';
            }
        $html .= '</table>';
        return $html;
    }

}
