<?php
declare(strict_types=1);

namespace App\Policy;

use Cake\Http\ServerRequest;
use Authorization\Policy\RequestPolicyInterface;
use Authorization\Policy\ResultInterface;
use Authorization\IdentityInterface;

class RequestPolicy implements RequestPolicyInterface
{

    public function canAccess(?IdentityInterface $identity, ServerRequest $request): bool|ResultInterface
    {
        
        $plugin = $request->getParam('plugin');
        $controller = $request->getParam('controller');

        if ($plugin == 'DebugKit') {
            return true;
        }

        $policy = match($plugin) {
            'Admin' => 'App\\Policy\\AdminPolicy',
            default => 'App\\Policy\\' . $controller . 'Policy',
        };

        if (class_exists($policy)) {
            return (new $policy())->canAccess($identity, $request);
        }

        // !sic default == true to throw correct 404Error for not available files /files/uploadify/users/thumbs-150/39430.jpeg
        return true;

    }

}