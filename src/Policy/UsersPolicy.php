<?php
declare(strict_types=1);

namespace App\Policy;

use Cake\Http\ServerRequest;
use Authorization\Policy\RequestPolicyInterface;
use Authorization\Policy\ResultInterface;

class UsersPolicy implements RequestPolicyInterface
{

    public function canAccess($identity, ServerRequest $request): bool|ResultInterface
    {

        switch($request->getParam('action')) {
            case 'passwortAendern':
            case 'profil':
            case 'welcome':
                return $identity !== null;
            case 'add':
                return $identity->isAdmin();
        }

        return true;

    }

}