<?php
declare(strict_types=1);

namespace App\Policy;

use Cake\Http\ServerRequest;
use Authorization\Policy\RequestPolicyInterface;

class BlogsPolicy implements RequestPolicyInterface
{

    public function canAccess($identity, ServerRequest $request)
    {
        return true;
    }

}