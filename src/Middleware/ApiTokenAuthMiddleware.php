<?php
declare(strict_types=1);

namespace App\Middleware;

use Cake\Http\Response;
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
            return $this->createErrorResponse('API token is required. Please provide a valid token in the Authorization header as Bearer token.', 401);
        }

        /** @var ApiTokensTable $apiTokensTable */
        $apiTokensTable = $this->fetchTable('ApiTokens');
        $apiToken = $apiTokensTable->findByToken($token);

        if ($apiToken === null) {
            return $this->createErrorResponse('Invalid or inactive API token', 401);
        }

        // Check if token has expired
        if ($apiToken->expires_at !== null && $apiToken->expires_at < new \DateTime()) {
            return $this->createErrorResponse('API token has expired', 401);
        }

        // Validate allowed search terms if city parameter is present
        $queryParams = $request->getQueryParams();
        if (isset($queryParams['city'])) {
            $city = (string) $queryParams['city'];
            if (!$apiToken->isSearchTermAllowed($city)) {
                return $this->createErrorResponse('Access to this city is not allowed with this API token', 401);
            }
        }

        // Update last_used timestamp
        $apiTokensTable->updateLastUsed($apiToken->id);

        // Add token to request attributes for later use
        $request = $request->withAttribute('apiToken', $apiToken);

        return $handler->handle($request);
    }

    /**
     * Create error response with CORS headers
     */
    private function createErrorResponse(string $message, int $statusCode): Response
    {
        $response = new Response();
        
        /** @var \Cake\Http\ServerRequest $serverRequest */
        $serverRequest = \Cake\Routing\Router::getRequest();
        
        $response = $response->cors($serverRequest)
            ->allowOrigin(['*'])
            ->allowMethods(['GET'])
            ->allowHeaders(['Authorization', 'Content-Type'])
            ->build();
        
        /* @phpstan-ignore-next-line */    
        return $response
            ->withStatus($statusCode)
            ->withType('application/json')
            ->withStringBody(json_encode(['error' => $message]));
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
