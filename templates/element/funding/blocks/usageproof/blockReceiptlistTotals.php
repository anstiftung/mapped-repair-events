<?php
declare(strict_types=1);

use App\Model\Entity\Funding;

echo '<div class="total-wrapper">';
    echo '<p class="total">Fördersumme: ' . $this->MyNumber->formatAsDecimal($funding->budgetplan_total_with_limit) . ' €</p>';
    echo '<p class="total">Belegte Gesamtsumme: ' . $this->MyNumber->formatAsDecimal($funding->receiptlist_total) . ' €</p>';
    if ($funding->receiptlist_difference > 0) {
        echo '<p class="total">Restbetrag: <b>' . $this->MyNumber->formatAsDecimal($funding->receiptlist_difference) . ' €</b></p>';
    }
echo '</div>';

echo '<div class="inner-wrapper">';
    $renderedFields = Funding::getRenderedFields(Funding::FIELDS_FUNDINGRECEIPTLIST_PAYBACK_CHECKBOX, 'fundingusageproof', $this->Form, $disabled);
    $renderedFields = str_replace('{RESTBETRAG}', (string) $this->MyNumber->formatAsDecimal($funding->receiptlist_difference), $renderedFields);
    $renderedFields = str_replace('{UID}', (string) $funding->uid, $renderedFields);
    echo $renderedFields;
echo '</div>';
