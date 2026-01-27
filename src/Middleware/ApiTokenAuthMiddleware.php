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
use Cake\Log\Log;

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
        
        // Handle CORS preflight OPTIONS request
        if ($request->getMethod() === 'OPTIONS') {
            $response = new Response();
            return $response
                ->withStatus(200)
                ->withHeader('Access-Control-Allow-Origin', '*')
                ->withHeader('Access-Control-Allow-Methods', 'GET, OPTIONS')
                ->withHeader('Access-Control-Allow-Headers', 'Authorization, Content-Type')
                ->withHeader('Access-Control-Max-Age', '86400');
        }
        
        $token = $this->extractToken($request);

        if ($token === null) {
            return $this->createErrorResponse($request, 'API token is required. Please provide a valid token in the Authorization header as Bearer token.', 401);
        }

        /** @var ApiTokensTable $apiTokensTable */
        $apiTokensTable = $this->fetchTable('ApiTokens');
        $apiToken = $apiTokensTable->findByToken($token);

        if ($apiToken === null) {
            return $this->createErrorResponse($request, 'Invalid or inactive API token', 401);
        }

        if ($apiToken->isExpired()) {
            return $this->createErrorResponse($request, 'API token has expired', 401);
        }

        // Validate allowed domain (check origin of the request)
        $origin = $this->extractOriginDomain($request);
        
        if (!empty($origin) && !$apiToken->isDomainAllowed($origin)) {
            $allowedDomains = json_decode($apiToken->allowed_domains, true) ?: [];
            $allowedDomainsList = !empty($allowedDomains) ? implode(', ', $allowedDomains) : 'none';
            Log::error('Access from this domain is not allowed with this API token. Allowed domains: ' . $allowedDomainsList);
            return $this->createErrorResponse($request, 'Invalid or inactive API token', 401);
        }

        // Validate allowed search terms if city parameter is present
        $queryParams = $request->getQueryParams();
        if (isset($queryParams['city'])) {
            $city = (string) $queryParams['city'];
            if (!$apiToken->isSearchTermAllowed($city)) {
                $allowedTerms = json_decode($apiToken->allowed_search_terms, true) ?: [];
                $allowedTermsList = !empty($allowedTerms) ? implode(', ', $allowedTerms) : 'none';
                return $this->createErrorResponse(
                    $request,
                    'Access to this city is not allowed with this API token. Allowed search terms: ' . $allowedTermsList,
                    401,
                );
            }
        }

        // Update last_used timestamp
        $apiTokensTable->updateLastUsed($apiToken->id);

        // Add token to request attributes for later use
        $request = $request->withAttribute('apiToken', $apiToken);

        // Process the request through the rest of the middleware stack
        $response = $handler->handle($request);
        
        // Ensure CORS headers are present on all responses
        if ($response instanceof Response) {
            $response = $response
                ->withHeader('Access-Control-Allow-Origin', '*')
                ->withHeader('Access-Control-Allow-Methods', 'GET')
                ->withHeader('Access-Control-Allow-Headers', 'Authorization, Content-Type');
        }
        
        return $response;
    }

    /**
     * Create error response with CORS headers
     */
    private function createErrorResponse(ServerRequestInterface $request, string $message, int $statusCode): Response
    {
        $response = new Response();
        
        return $response
            ->withStatus($statusCode)
            ->withType('application/json')
            ->withStringBody(json_encode(['error' => $message]))
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Methods', 'GET')
            ->withHeader('Access-Control-Allow-Headers', 'Authorization, Content-Type');
    }

    /**
     * Extract token from request
     *
     * Checks Authorization header (Bearer token)
     */
    private function extractToken(ServerRequestInterface $request): ?string {
        // Check Authorization header
        $authHeader = $request->getHeaderLine('Authorization');
        if (!empty($authHeader) && preg_match('/^Bearer\s+(.+)$/i', $authHeader, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Extract the origin domain from the request
     *
     * Checks Origin header first, then falls back to Referer header
     */
    private function extractOriginDomain(ServerRequestInterface $request): ?string
    {
        // Check Origin header (preferred for CORS requests)
        $origin = $request->getHeaderLine('Origin');
        if (!empty($origin)) {
            $parsed = parse_url($origin);
            if (is_array($parsed) && isset($parsed['host'])) {
                return $parsed['host'];
            }
        }

        // Fallback to Referer header
        $referer = $request->getHeaderLine('Referer');
        if (!empty($referer)) {
            $parsed = parse_url($referer);
            if (is_array($parsed) && isset($parsed['host'])) {
                return $parsed['host'];
            }
        }

        return null;
    }
}
