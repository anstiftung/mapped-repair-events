<?php
declare(strict_types=1);

namespace App\Middleware;

use Cake\Http\Exception\UnauthorizedException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Cake\ORM\Locator\LocatorAwareTrait;
use App\Model\Table\ApiTokensTable;

/**
 * API Token Authentication Middleware
 *
 * Validates API tokens for protected routes
 */
class ApiTokenAuthMiddleware implements MiddlewareInterface
{
    use LocatorAwareTrait;

    /**
     * Process the request
     */
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler,
    ): ResponseInterface {
        $token = $this->extractToken($request);

        if ($token === null) {
            throw new UnauthorizedException('API token is required. Please provide a valid token in the Authorization header as Bearer token.');
        }

        /** @var ApiTokensTable $apiTokensTable */
        $apiTokensTable = $this->fetchTable('ApiTokens');
        $apiToken = $apiTokensTable->findByToken($token);

        if ($apiToken === null) {
            throw new UnauthorizedException('Invalid or inactive API token');
        }

        // Check if token has expired
        if ($apiToken->expires_at !== null && $apiToken->expires_at < new \DateTime()) {
            throw new UnauthorizedException('API token has expired');
        }

        // Update last_used timestamp
        $apiTokensTable->updateLastUsed($apiToken->id);

        // Add token to request attributes for later use
        $request = $request->withAttribute('apiToken', $apiToken);

        return $handler->handle($request);
    }

    /**
     * Extract token from request
     *
     * Checks Authorization header (Bearer token)
     */
    private function extractToken(ServerRequestInterface $request): ?string
    {
        // Check Authorization header
        $authHeader = $request->getHeaderLine('Authorization');
        if (!empty($authHeader) && preg_match('/^Bearer\s+(.+)$/i', $authHeader, $matches)) {
            return $matches[1];
        }

        return null;
    }
}
