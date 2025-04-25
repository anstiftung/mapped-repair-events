<?php
declare(strict_types=1);

namespace App\Policy;

use Cake\Http\ServerRequest;
use Authorization\Policy\RequestPolicyInterface;
use Authorization\Policy\ResultInterface;
use Authorization\IdentityInterface;

class UsersPolicy implements RequestPolicyInterface
{

    public function canAccess(?IdentityInterface $identity, ServerRequest $request): bool|ResultInterface
    {
        return match ($request->getParam('action')) {
            'passwortAendern', 'profil', 'welcome' => $identity !== null,
            'add' => $identity->isAdmin(),
            default => true,
        };
    }

}