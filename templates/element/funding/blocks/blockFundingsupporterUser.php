<?php

use App\Model\Entity\Funding;

echo $this->Form->fieldset(
    Funding::getRenderedFields(Funding::FIELDS_FUNDINGSUPPORTER_USER, 'fundingsupporter', $this->Form, $disabled),
    [
        'legend' => 'Ansprechpartner*in der Trägerorganisation',
    ]
);
