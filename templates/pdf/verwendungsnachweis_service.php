<?php
declare(strict_types=1);

use App\Model\Entity\Fundingbudgetplan;
use App\Model\Entity\Funding;
use Cake\Core\Configure;

$pdf->setDefaults();
$pdf->Ln(35);

$pdf->SetFontSizeBig();
$html = '<b>Verwendungsnachweis vom ' . $timestamp->i18nFormat(Configure::read('DateFormat.de.DateNTimeLongWithSeconds')) . ' - UID: ' . $funding->uid . '</b>';
$pdf->writeHTML($html, true, false, true, false, '');
$pdf->SetFontSizeDefault();
$pdf->Ln(10);

$html = '<b>' . Funding::FIELDS_FUNDINGUSAGEPROOF_LABEL . '</b>';
$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Ln(3);

$html = '<p>' . $description['main_description']['value'] . '</p>';
$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Ln(10);

$html = '<b>' . Funding::FIELDS_FUNDINGRECEIPTLIST_LABEL . '</b>';
$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Ln(3);

$pdf->SetFontSizeSmall();
$html = '';
$receiptlistTableArgs = ['100%', '34%', '20%', '10%', '14%', '12%', '10%', 'left', 'left', 'left', 'left', 'left', 'right'];

foreach($funding->grouped_valid_receiptlists as $typeId => $fundingreceiptlists) {
    $html .= '<b>' . Fundingbudgetplan::TYPE_MAP[$typeId] . '</b><br />';
    $preparedDataForTable = [];
    $preparedDataForTable[] = [
        'columnA' => Funding::FIELDS_FUNDINGRECEIPTLIST[2]['options']['placeholder'],
        'columnB' => Funding::FIELDS_FUNDINGRECEIPTLIST[3]['options']['placeholder'],
        'columnC' => Funding::FIELDS_FUNDINGRECEIPTLIST[4]['options']['placeholder'],
        'columnD' => Funding::FIELDS_FUNDINGRECEIPTLIST[5]['options']['placeholder'],
        'columnE' => Funding::FIELDS_FUNDINGRECEIPTLIST[6]['options']['placeholder'],
        'columnF' => Funding::FIELDS_FUNDINGRECEIPTLIST[7]['options']['placeholder'],
    ];
    foreach($fundingreceiptlists as $fundingreceiptlist) {
        $preparedDataForTable[] = [
            'columnA' => $fundingreceiptlist->description,
            'columnB' => $fundingreceiptlist->recipient,
            'columnC' => $fundingreceiptlist->receipt_type,
            'columnD' => $fundingreceiptlist->payment_date->format('d.m.Y'),
            'columnE' => $fundingreceiptlist->receipt_number,
            'columnF' => $this->MyNumber->formatAsDecimal($fundingreceiptlist->amount) . ' €',
        ];
    }
    $html .= $pdf->getFundingReceiptlistAsTable($preparedDataForTable, ...$receiptlistTableArgs);
    $preparedSumDataForTable = [
        [
            'columnA' => '',
            'columnB' => '',
            'columnC' => '',
            'columnD' => '',
            'columnE' => 'Summe',
            'columnF' => $this->MyNumber->formatAsDecimal($funding->grouped_valid_receiptlists_totals[$typeId]) . ' €',
        ],
    ];
    $html .= $pdf->getFundingReceiptlistAsTable($preparedSumDataForTable, ...$receiptlistTableArgs);
}
$pdf->writeHTML($html, true, false, true, false, '');
$pdf->SetFontSizeDefault();

$preparedSumDataForTable = [
    [
        'label' => '<b>Belegte Gesamtsumme</b>',
        'value' => '<b>' . $this->MyNumber->formatAsDecimal($funding->receiptlist_total) . ' €</b>',
    ],
];

$tableArgs = ['100%', '85%', '15%', 'left', 'right'];
$html = $pdf->getFundingDataAsTable($preparedSumDataForTable, ...$tableArgs);
$pdf->writeHTML($html, true, false, true, false, '');


$pdf->Ln(5);
$html = '<b>' . Funding::FIELDS_USAGEPROOF_CHECKBOXES_LABEL . '</b>';
$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Ln(3);

$html = '';
foreach($checkboxes as $checkbox) {
    if ($funding->fundingdata[$checkbox['name']] == 1) {
        $html .= 'Bestätigt';
    } else {
        $html .= 'Nicht bestätigt';
    }
    $html .= ': "' .$checkbox['label'] . '"<br /><br />';
}

$pdf->SetFontSizeDefault();
$pdf->writeHTML($html, true, false, true, false, '');
