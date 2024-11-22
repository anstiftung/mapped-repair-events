<?php

use App\Model\Entity\Funding;

echo '<fieldset class="full-width">';
    echo '<legend>Geplantes Vorhaben</legend>';
    echo '<div class="verification-wrapper ' . $funding->description_status_css_class . '">';
        echo '<p>' . $funding->description_status_human_readable . '</p>';
    echo '</div>';
        echo Funding::getRenderedFields(Funding::FIELDS_FUNDING_DESCRIPTION, 'funding', $this->Form);
    echo '</div>';
echo '</fieldset>';
