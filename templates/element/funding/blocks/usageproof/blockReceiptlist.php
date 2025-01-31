<?php
declare(strict_types=1);

use App\Model\Entity\Funding;

echo '<fieldset class="fundingbudgetplan full-width">';
    echo '<legend>' . Funding::FIELDS_FUNDINGRECEIPTLIST_LABEL . '</legend>';

    echo '<div class="verification-wrapper ' . $funding->receiptlist_status_css_class . '">';
        echo '<p>' . $funding->receiptlist_status_human_readable . '</p>';
    echo '</div>';

    echo '<div style="margin-bottom:5px;padding:10px;">';
        echo '<p>Hilfetext Belegliste.</p>';
    echo '</div>';

    echo '<div class="row-wrapper">';
        foreach($funding->fundingreceiptlists as $fundingreceiptlistIndex => $fundingreceiptlist) {
            echo '<div class="row">';
                echo Funding::getRenderedFields(Funding::FIELDS_FUNDINGRECEIPTLIST, 'fundingreceiptlists.'.$fundingreceiptlistIndex, $this->Form, false, $fundingreceiptlist);
            echo '</div>';
        }
    echo '</div>';

    echo '<div class="add-receiptlist-button-wrapper">';
        echo $this->Form->button('Beleg hinzufügen', [
            'type' => 'button',
            'id' => 'add-receiptlist-button-' . $funding->uid,
            'class' => 'rounded',
        ]);
        $this->element('addScript', ['script' =>
            JS_NAMESPACE.".Funding.bindAddReceiptlistButton(".$funding->uid.");"
        ]);
    echo '</div>';

    echo '</fieldset>';


?>