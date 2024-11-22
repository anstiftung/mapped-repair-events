<?php

use App\Model\Table\FundingsTable;
use App\Model\Entity\Funding;

echo '<fieldset class="fundingbudgetplan full-width">';
echo '<legend>Kostenplan</legend>';

echo '<div class="verification-wrapper ' . $funding->budgetplan_status_css_class . '">';
    echo '<p>' . $funding->budgetplan_status_human_readable . '</p>';
echo '</div>';

$shownFundingbudgetplans = array_slice($funding->fundingbudgetplans, 0, FundingsTable::FUNDINGBUDGETPLANS_COUNT_VISIBLE, true);
$hiddenFundingbudgetplans = array_slice($funding->fundingbudgetplans, FundingsTable::FUNDINGBUDGETPLANS_COUNT_VISIBLE, FundingsTable::FUNDINGBUDGETPLANS_COUNT, true);

echo '<div class="row-wrapper">';
    foreach($shownFundingbudgetplans as $fundingbudgetplanIndex => $fundingbudgetplan) {
        echo '<div class="row">';
            echo Funding::getRenderedFields(Funding::FIELDS_FUNDINGBUDGETPLAN, 'fundingbudgetplans.'.$fundingbudgetplanIndex, $this->Form, $fundingbudgetplan);
        echo '</div>';
    }
echo '</div>';

$nonEmptyHiddenRecordsExist = count(array_filter($hiddenFundingbudgetplans, function($fundingbudgetplan) {
    return $fundingbudgetplan->is_not_empty;
})) > 0;
$this->element('addScript', ['script' =>
    JS_NAMESPACE . ".Helper.bindShowMoreLink(" . ($nonEmptyHiddenRecordsExist ? 'true'  : 'false') . ");
"]);
echo '<a href="javascript:void(0);" class="show-more-link" style="margin-left:5px;line-height:30px;"><i class="fa fa-plus-circle"></i> Mehr Felder anzeigen</a>';

echo '<div class="row-wrapper" style="display:none;">';
    foreach($hiddenFundingbudgetplans as $fundingbudgetplanIndex => $fundingbudgetplan) {
        echo '<div class="row">';
            echo Funding::getRenderedFields(Funding::FIELDS_FUNDINGBUDGETPLAN, 'fundingbudgetplans.'.$fundingbudgetplanIndex, $this->Form, $fundingbudgetplan);
        echo '</div>';
    }
echo '</div>';

echo '<div class="fundingbudgets-total-wrapper">';
    echo '<p class="total">Kosten gesamt: <b>' . $this->MyNumber->formatAsDecimal($funding->budgetplan_total) . ' €</b></p>';
    if ($funding->budgetplan_total > Funding::MAX_FUNDING_SUM) {
        echo '<p>Maximale Fördersumme: ' . $this->MyNumber->formatAsDecimal(Funding::MAX_FUNDING_SUM) . ' €</p>';
    }
echo '</div>';

echo '</fieldset>';


?>