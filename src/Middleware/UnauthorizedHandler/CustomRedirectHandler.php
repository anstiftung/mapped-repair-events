<?php
declare(strict_types=1);

namespace App\Middleware\UnauthorizedHandler;

use Authorization\Exception\Exception;
use Authorization\Middleware\UnauthorizedHandler\RedirectHandler;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class CustomRedirectHandler extends RedirectHandler {

    /**
     * @param array<string, string> $options
     */
    public function handle( Exception $exception, ServerRequestInterface $request, array $options = [] ): ResponseInterface {
        $response = parent::handle( $exception, $request, $options );
        /* @phpstan-ignore-next-line */
        $request->getFlash()->set('Zugriff verweigert, bitte melde dich an.', [
            'element' => 'default',
            'params' => [
                'class' => 'error'
            ]
        ]);
        return $response;
    }
}