<?php

use App\Model\Entity\Funding;

echo $this->Form->fieldset(
    Funding::getRenderedFields(Funding::FIELDS_OWNER_USER, 'owner_user', $this->Form),
    [
        'legend' => 'Personenbezogene Daten Ansprechpartner*in (UID: ' . $funding->owner_user->uid . ')',
    ]
);
