<?php
declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link      https://cakephp.org CakePHP(tm) Project
 * @since     3.3.0
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 */

namespace App;

use Cake\Core\Configure;
use Cake\Http\ServerRequest;
use App\Policy\RequestPolicy;
use Cake\Http\BaseApplication;
use Cake\Http\MiddlewareQueue;
use Authorization\Policy\MapResolver;
use Authorization\AuthorizationService;
use Authentication\AuthenticationService;
use Cake\Routing\Middleware\AssetMiddleware;
use Psr\Http\Message\ServerRequestInterface;
use Cake\Routing\Middleware\RoutingMiddleware;
use Cake\Core\Exception\MissingPluginException;
use Authorization\AuthorizationServiceInterface;
use Cake\Error\Middleware\ErrorHandlerMiddleware;
use Authentication\AuthenticationServiceInterface;
use Cake\Http\Middleware\CsrfProtectionMiddleware;
use Authorization\Middleware\AuthorizationMiddleware;
use Authentication\Middleware\AuthenticationMiddleware;
use Authorization\AuthorizationServiceProviderInterface;
use Authentication\AuthenticationServiceProviderInterface;
use Authentication\Identifier\Resolver\OrmResolver;
use Authorization\Middleware\RequestAuthorizationMiddleware;
use Authorization\Exception\MissingIdentityException;
use Authorization\Exception\ForbiddenException;
use Cake\Http\Middleware\BodyParserMiddleware;
use Cake\Http\Middleware\EncryptedCookieMiddleware;
use Cake\I18n\DateTime;

class Application extends BaseApplication
    implements AuthenticationServiceProviderInterface, AuthorizationServiceProviderInterface
{

    /**
     * Load all the application configuration and bootstrap logic.
     *
     * @return void
     */
    public function bootstrap(): void
    {
        // Call parent to load bootstrap from files.
        parent::bootstrap();

        $this->addPlugin('Authentication');
        $this->addPlugin('Authorization');

        if (Configure::read('debug')) {
            Configure::write('DebugKit.ignoreAuthorization', true);
            $this->addPlugin('DebugKit', ['bootstrap' => true]);
        }

        $this->addPlugin('Migrations');
        $this->addPlugin('AssetCompress', ['bootstrap' => true]);

        $this->addPlugin('Admin', [
            'bootstrap' => false,
            'routes' => true,
            'autoload' => true
        ]);

        $this->addPlugin('Feed', ['bootstrap' => true]);

    }

    /**
     * Setup the middleware queue your application will use.
     *
     * @param \Cake\Http\MiddlewareQueue $middlewareQueue The middleware queue to setup.
     * @return \Cake\Http\MiddlewareQueue The updated middleware queue.
     */
    public function middleware(MiddlewareQueue $middlewareQueue): MiddlewareQueue
    {
        $middlewareQueue

        // Handle plugin/theme assets like CakePHP normally does.
        ->add(new AssetMiddleware([
            'cacheTime' => Configure::read('Asset.cacheTime'),
        ]))

        ->add(new CsrfProtectionMiddleware())

        ->add(new RoutingMiddleware($this))

        ->add (new EncryptedCookieMiddleware(
            ['CookieAuth'],
            Configure::read('Security.cookieKey')
        ))
    
        ->add(new AuthenticationMiddleware($this))

        ->add(new BodyParserMiddleware())

        ->add(
            new AuthorizationMiddleware($this, [
                'unauthorizedHandler' => [
                    'className' => 'CustomRedirect',
                    'url' => '/users/login',
                    'exceptions' => [
                        MissingIdentityException::class,
                        ForbiddenException::class,
                    ],
                ],
            ])
        )

        ->add(new RequestAuthorizationMiddleware())

        // Catch any exceptions in the lower layers, and make an error page/response
        // needs to be added as last middleware to make production error page work (identity, csrf token)
        ->add(new ErrorHandlerMiddleware(Configure::read('Error')))

        ;

        return $middlewareQueue;
    }

    /**
     * Bootrapping for CLI application.
     *
     * That is when running commands.
     *
     * @return void
     */
    protected function bootstrapCli(): void
    {
        try {
            $this->addPlugin('Bake');
        } catch (MissingPluginException $e) {
            // Do not halt if the plugin is missing
        }

        $this->addPlugin('Migrations');

    }

    public function getAuthenticationService(ServerRequestInterface $request): AuthenticationServiceInterface
    {

        $fields = [
            'username' => 'email',
            'password' => 'password',
        ];
        $service = new AuthenticationService();
        $service->setConfig([
            'queryParam' => 'redirect',
        ]);

        $service->loadIdentifier('Authentication.Password', [
            'resolver' => [
                'className' => OrmResolver::class,
                'finder' => 'auth', // UsersTable::findAuth
            ],
            'fields' => $fields,
        ]);
        
        // Load the authenticators
        $service->loadAuthenticator('Authentication.Session');
        $service->loadAuthenticator('Authentication.Form', [
            'fields' => [
                'username' => 'email',
            ],
        ]);
        
        $service->loadAuthenticator('Authentication.Cookie', [
            'fields' => $fields,
            'loginUrl' => '/users/login',
            'cookie' => [
                'expires' => new DateTime('+30 day'),
            ],
        ]);


        return $service;
    }

    public function getAuthorizationService(ServerRequestInterface $request): AuthorizationServiceInterface
    {
        $mapResolver = new MapResolver();
        $mapResolver->map(ServerRequest::class, RequestPolicy::class);
        return new AuthorizationService($mapResolver);
    }

}