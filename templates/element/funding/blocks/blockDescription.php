<?php

use App\Model\Entity\Funding;

echo '<fieldset class="full-width">';
    echo '<legend>Kurzbeschreibung Vorhaben</legend>';
    echo '<div class="verification-wrapper ' . $funding->description_status_css_class . '">';
        echo '<p>' . $funding->description_status_human_readable . '</p>';
    echo '</div>';
    echo '<div>';
        echo '<p style="margin-bottom:10px;padding:5px;">Beschreibe kurz und prägnant, was ihr vorhabt und zu welchen Zwecken und Zielen die Mittel im Sinne der Förderrichtlinien eingesetzt werden sollen.</p>';
        echo Funding::getRenderedFields(Funding::FIELDS_FUNDINGDATA_DESCRIPTION, 'fundingdata', $this->Form);
    echo '</div>';
echo '</fieldset>';
