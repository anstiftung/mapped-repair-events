<?php
declare(strict_types=1);

namespace App\Policy;

use Cake\Http\ServerRequest;
use Authorization\Policy\RequestPolicyInterface;

class UsersPolicy implements RequestPolicyInterface
{

    public function canAccess($identity, ServerRequest $request)
    {

        switch($request->getParam('action')) {
            case 'passwortAendern':
            case 'profil':
            case 'welcome':
                return $identity !== null;
                break;
            case 'add':
                return $identity->isAdmin();
                break;
        }

        return true;

    }

}