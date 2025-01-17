<?php
declare(strict_types=1);

namespace App\Identifier;

use ArrayAccess;
use Authentication\Identifier\PasswordIdentifier;

class NonPrivatizedPasswordIdentifier extends PasswordIdentifier
{
    public function identify(array $credentials): ArrayAccess|array|null
    {
        if (!isset($credentials[self::CREDENTIAL_USERNAME])) {
            return null;
        }

        $identity = $this->_findIdentity($credentials[self::CREDENTIAL_USERNAME]);
        if (!is_null($identity)) {
            /* @phpstan-ignore-next-line */
            $identity->revertPrivatizeData();
        }

        if (array_key_exists(self::CREDENTIAL_PASSWORD, $credentials)) {
            $password = $credentials[self::CREDENTIAL_PASSWORD];
            if (!$this->_checkPassword($identity, $password)) {
                return null;
            }
        }

        return $identity;
    }
}
