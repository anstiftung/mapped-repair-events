<?php

use App\Model\Entity\Funding;

echo $this->Form->fieldset(
    Funding::getRenderedFields(Funding::FIELDS_FUNDINGSUPPORTER_BANK, 'fundingsupporter', $this->Form),
    [
        'legend' => 'Bankverbindung der Trägerorganisation',
    ]
);
