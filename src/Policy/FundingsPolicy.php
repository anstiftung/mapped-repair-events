<?php
declare(strict_types=1);

namespace App\Policy;

use Cake\Http\ServerRequest;
use Authorization\Policy\RequestPolicyInterface;
use Authorization\Policy\ResultInterface;
use Cake\Datasource\FactoryLocator;

class FundingsPolicy implements RequestPolicyInterface
{

    public function canAccess($identity, ServerRequest $request): bool|ResultInterface
    {
        if (is_null($identity)) {
            return false;
        }

        if (!($identity->isAdmin() || $identity->isOrga())) {
            return false;
        }

        return true;

    }

}