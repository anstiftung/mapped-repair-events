<?php
declare(strict_types=1);

use App\Model\Entity\Funding;

echo $this->Form->fieldset(
    Funding::getRenderedFields(Funding::FIELDS_FUNDINGSUPPORTER_USER, 'fundingsupporter', $this->Form, $disabled),
    [
        'legend' => Funding::FIELDS_FUNDINGSUPPORTER_USER_LABEL,
    ]
);
