<?php

use App\Model\Entity\Funding;

echo $this->Form->fieldset(
    Funding::getRenderedFields(Funding::FIELDS_FUNDINGSUPPORTER_ORGANIZATION, 'fundingsupporter', $this->Form, $disabled),
    [
        'legend' => 'Stammdaten der Trägerorganisation',
    ]
);
