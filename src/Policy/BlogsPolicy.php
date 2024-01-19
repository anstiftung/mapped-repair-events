<?php
declare(strict_types=1);

namespace App\Policy;

use Cake\Http\ServerRequest;
use Authorization\Policy\RequestPolicyInterface;
use Authorization\Policy\ResultInterface;

class BlogsPolicy implements RequestPolicyInterface
{

    public function canAccess($identity, ServerRequest $request): bool|ResultInterface
    {
        return true;
    }

}