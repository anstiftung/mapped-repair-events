<?php

use App\Model\Entity\Fundingbudgetplan;
use App\Model\Entity\Funding;

$pdf->setDefaults();

$html = '<b>Förderantrag vom ' . $formattedCurrentDate . ' - UID: ' . $funding->uid . '</b>';
$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Ln(15);

$i = 0;
foreach($blocks as $block) {
    $html = '<b>' . $block['name'] . '</b>';
    $pdf->writeHTML($html, true, false, true, false, '');
    $pdf->Ln(3);
    
    $html = $pdf->getFundingDataAsTable($block['fields']);
    $pdf->writeHTML($html, true, false, true, false, '');
    $pdf->Ln(3);

    if ($i == 3) {
        $pdf->addPage();
        $pdf->Ln(25);
    }

    $i++;
}

$html = '<b>' . Funding::FIELDS_FUNDINGDATA_DESCRIPTION_LABEL . '</b>';
$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Ln(3);

$html = '<p>' . $description['description']['value'] . '</p>';
$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Ln(10);

$html = '<b>' . Funding::FIELDS_FUNDINGBUDGETPLAN_LABEL . '</b>';
$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Ln(3);

$html = '';
foreach($funding->grouped_valid_budgetplans as $typeId => $fundingbudgetplans) {
    $html .= '<b>' . Fundingbudgetplan::TYPE_MAP[$typeId] . '</b><br />';
    $preparedDataForTable = [];
    foreach($fundingbudgetplans as $fundingbudgetplan) {
        $preparedDataForTable[] = [
            'label' => $fundingbudgetplan->description,
            'value' => $this->MyNumber->formatAsDecimal($fundingbudgetplan->amount) . ' €',
        ];
    }
    $html .= $pdf->getFundingDataAsTable($preparedDataForTable, '75%', '70%', '30%', 'left', 'right');
    $preparedSumDataForTable = [
        [
            'label' => '',
            'value' => '<b>' . $this->MyNumber->formatAsDecimal($funding->grouped_valid_budgetplans_totals[$typeId]) . ' €</b>',
        ],
    ];
    $html .= $pdf->getFundingDataAsTable($preparedSumDataForTable, '75%', '70%', '30%', 'left', 'right');
}
$pdf->writeHTML($html, true, false, true, false, '');

$preparedSumDataForTable = [
    [
        'label' => '<b>Gesamtsumme</b>',
        'value' => '<b>' . $this->MyNumber->formatAsDecimal($funding->budgetplan_total) . ' €</b>',
    ],
];

$html = $pdf->getFundingDataAsTable($preparedSumDataForTable, '75%', '70%', '30%', 'left', 'right');
$pdf->SetFontSizeDefault();
$pdf->writeHTML($html, true, false, true, false, '');
