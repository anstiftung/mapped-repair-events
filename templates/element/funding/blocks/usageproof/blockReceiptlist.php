<?php
declare(strict_types=1);

use App\Model\Entity\Funding;

echo '<fieldset class="fundinglist fundingreceiptlist full-width">';
    echo '<legend>' . Funding::FIELDS_FUNDINGRECEIPTLIST_LABEL . '</legend>';

    echo '<div class="verification-wrapper ' . $funding->receiptlist_status_css_class . '">';
        echo '<p>' . $funding->receiptlist_status_human_readable . '</p>';
    echo '</div>';

    echo '<div style="margin-bottom:5px;padding:10px;">';
        echo '<p>Eine Beleg-Zeile wird nur dann permanent gespeichert, wenn nach Klicken auf "Zwischenspeichern" alle Felder gültig (nicht rot) sind.<br />
                Bei Fehlern bleiben die zuletzt gültigen Werte gespeichert.</p>';
    echo '</div>';

    echo '<div class="row-wrapper">';
        foreach($funding->fundingreceiptlists as $fundingreceiptlistIndex => $fundingreceiptlist) {
            echo '<div class="row">';
                echo Funding::getRenderedFields(Funding::FIELDS_FUNDINGRECEIPTLIST, 'fundingreceiptlists.'.$fundingreceiptlistIndex, $this->Form, false, $fundingreceiptlist);
            echo '</div>';
        }
    echo '</div>';

    echo '<div class="add-receiptlist-button-wrapper">';
        echo $this->Form->button('Zwischenspeichern und Beleg hinzufügen', [
            'type' => 'button',
            'id' => 'add-receiptlist-button',
            'class' => 'rounded',
            'escape' => false,
        ]);
        $this->element('addScript', ['script' =>
            JS_NAMESPACE.".Funding.bindAddReceiptlistButton();".
            JS_NAMESPACE.".Funding.bindDeleteReceiptlistCheckboxClickHandler();"
        ]);
    echo '</div>';

    echo '<div class="total-wrapper">';
        echo '<p class="total">Fördersumme: ' . $this->MyNumber->formatAsDecimal($funding->budgetplan_total_with_limit) . ' €</p>';
        echo '<p class="total">Belegsumme: ' . $this->MyNumber->formatAsDecimal($funding->receiptlist_total) . ' €</p>';
        if ($funding->receiptlist_receipt_total_is_less_than_budgetplan_total) {
            echo '<p class="total">Restbetrag: <b>' . $this->MyNumber->formatAsDecimal($funding->receiptlist_difference) . ' €</b></p>';
            echo Funding::getRenderedFields(Funding::FIELDS_FUNDINGRECEIPTLIST_DIFFERENCE_CHECKBOX, 'fundingusageproof', $this->Form, $disabled);
        } else {
            echo '<p class="total">Belegsumme zu hoch: <b>' . $this->MyNumber->formatAsDecimal($funding->receiptlist_difference * -1) . ' €</b></p>';
        }
    echo '</div>';


    echo '</fieldset>';


?>