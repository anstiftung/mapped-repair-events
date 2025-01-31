<?php
declare(strict_types=1);

use App\Model\Entity\Funding;

echo '<fieldset class="fundinglist fundingreceiptlist full-width">';
    echo '<legend>' . Funding::FIELDS_FUNDINGRECEIPTLIST_LABEL . '</legend>';

    echo '<div class="verification-wrapper ' . $funding->receiptlist_status_css_class . '">';
        echo '<p>' . $funding->receiptlist_status_human_readable . '</p>';
    echo '</div>';

    echo '<div style="margin-bottom:5px;padding:10px;">';
        echo '<p>Ein Beleg wird nur dann zwischengespeichert, wenn alle Felder gültig sind</p>';
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

    echo '<div class="fundingbudgets-total-wrapper">';
        echo '<p class="total">Fördersumme: ' . $this->MyNumber->formatAsDecimal($funding->budgetplan_total_with_limit) . ' €</p>';
        echo '<p class="total">Gültige Belege: ' . $this->MyNumber->formatAsDecimal($funding->receiptlist_total * -1) . ' €</p>';
        echo '<p class="total">Differenz: <b>' . $this->MyNumber->formatAsDecimal($funding->receiptlist_difference) . ' €</b></p>';
    echo '</div>';


    echo '</fieldset>';


?>