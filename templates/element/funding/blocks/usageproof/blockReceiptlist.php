<?php
declare(strict_types=1);

use App\Model\Entity\Funding;

echo '<fieldset class="fundinglist fundingreceiptlist full-width">';
    echo '<legend>' . Funding::FIELDS_FUNDINGRECEIPTLIST_LABEL . '</legend>';

    echo '<div class="verification-wrapper ' . $funding->receiptlist_status_css_class . '">';
        echo '<p>' . $funding->receiptlist_status_human_readable . '</p>';
    echo '</div>';

    echo '<div style="margin-bottom:5px;padding:10px;">';
        echo '<ul style="list-style-type:disc;margin-left:10px;">
            <li>Eine Beleg-Zeile wird nur dann permanent gespeichert, wenn nach Klicken auf "Zwischenspeichern" alle Felder gültig (nicht rot) sind</li>
            <li>Bei Fehlern bleiben die zuletzt gültigen Werte gespeichert</li>
            <li>Fördersumme minus Belegsumme muss entweder <b>genau 0</b> ergeben</li>
            <li>oder die Belegsumme ist <b>kleiner</b> als die Fördersumme und das Häkchen bei "Rücküberweisung Restbetrag" muss gesetzt werden</li>
            <li>Wenn die Belegsumme größer als die Fördersumme ist, kann der Verwendungsnachweis so nicht eingereicht werden (Belege anpassen)</li>
        </ul>';
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

    echo $this->element('funding/blocks/usageproof/blockReceiptlistTotals', ['funding' => $funding, 'disabled' => false]);

    echo '</fieldset>';

?>