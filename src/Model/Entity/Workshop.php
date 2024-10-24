<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

class Workshop extends Entity
{

    protected array $_hidden = [
        'is_funding_allowed',
    ];

    protected array $_virtual = ['is_funding_allowed'];

    public function _getIsFundingAllowed(): bool {
        /* @phpstan-ignore-next-line */
        return $this->country_code == 'DE';
    }

}
