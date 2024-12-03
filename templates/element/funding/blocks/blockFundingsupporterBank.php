<?php

use App\Model\Entity\Funding;

echo $this->Form->fieldset(
    Funding::getRenderedFields(Funding::FIELDS_FUNDINGSUPPORTER_BANK, 'fundingsupporter', $this->Form, $disabled),
    [
        'legend' => Funding::FIELDS_FUNDINGSUPPORTER_BANK_LABEL,
    ]
);
