<?php
declare(strict_types=1);

use App\Model\Entity\Funding;

echo '<div class="total-wrapper">';
    echo '<p class="total">Fördersumme: ' . $this->MyNumber->formatAsDecimal($funding->budgetplan_total_with_limit) . ' €</p>';
    echo '<p class="total">Belegte Gesamtsumme: ' . $this->MyNumber->formatAsDecimal($funding->receiptlist_total) . ' €</p>';
    if ($funding->receiptlist_receipt_total_is_less_than_budgetplan_total) {
        echo '<p class="total">Restbetrag: <b>' . $this->MyNumber->formatAsDecimal($funding->receiptlist_difference) . ' €</b></p>';
        echo Funding::getRenderedFields(Funding::FIELDS_FUNDINGRECEIPTLIST_DIFFERENCE_CHECKBOX, 'fundingusageproof', $this->Form, $disabled);
    } else {
        echo '<p class="total">Belegte Gesamtsumme zu hoch: <b>' . $this->MyNumber->formatAsDecimal($funding->receiptlist_difference * -1) . ' €</b></p>';
    }
echo '</div>';
