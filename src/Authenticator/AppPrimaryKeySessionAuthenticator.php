<?php
declare(strict_types=1);

namespace App\Authenticator;

use ArrayAccess;
use Authentication\Authenticator\PrimaryKeySessionAuthenticator;
use Authentication\Authenticator\Result;
use Authentication\Authenticator\ResultInterface;
use Psr\Http\Message\ServerRequestInterface;

class AppPrimaryKeySessionAuthenticator extends PrimaryKeySessionAuthenticator
{

    /**
     * Authenticate a user using session data.
     * 
     * Handles migration from old sessions that stored the full entity
     * to new sessions that store only the primary key integer.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The request to authenticate with.
     * @return \Authentication\Authenticator\ResultInterface
     */
    public function authenticate(ServerRequestInterface $request): ResultInterface
    {
        $sessionKey = $this->getConfig('sessionKey');
        /** @var \Cake\Http\Session $session */
        $session = $request->getAttribute('session');

        $sessionData = $session->read($sessionKey);
        if (!$sessionData) {
            return new Result(null, Result::FAILURE_IDENTITY_NOT_FOUND);
        }

        // Old sessions stored the full entity object instead of just the ID.
        // Extract the primary key and rewrite the session to the new format.
        if ($sessionData instanceof ArrayAccess || is_array($sessionData)) {
            $idField = $this->getConfig('idField');
            $userId = $sessionData[$idField] ?? null;
            if ($userId === null) {
                $session->delete($sessionKey);
                return new Result(null, Result::FAILURE_IDENTITY_NOT_FOUND);
            }
            $session->write($sessionKey, $userId);
        }

        return parent::authenticate($request);
    }

}
