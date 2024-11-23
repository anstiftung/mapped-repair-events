<?php

use App\Model\Entity\Funding;

echo $this->Form->fieldset(
    Funding::getRenderedFields(Funding::FIELDS_WORKSHOP, 'workshop', $this->Form),
    [
        'legend' => 'Stammdaten der Reparatur-Initiative (UID: ' . $funding->workshop->uid . ')'
    ]
);
