<?php
/**
 * Routes configuration.
 *
 * In this file, you set up routes to your controllers and their actions.
 * Routes are very important mechanism that allows you to freely connect
 * different URLs to chosen controllers and their actions (functions).
 *
 * It's loaded within the context of `Application::routes()` method which
 * receives a `RouteBuilder` instance `$builder` as method argument.
 *
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */

use Cake\Core\Configure;
use Cake\Routing\Route\DashedRoute;
use Cake\Routing\RouteBuilder;
use Cake\Routing\Router;

/*
 * The default class to use for all routes
 *
 * The following route classes are supplied with CakePHP and are appropriate
 * to set as the default:
 *
 * - Route
 * - InflectedRoute
 * - DashedRoute
 *
 * If no call is made to `Router::defaultRouteClass()`, the class used is
 * `Route` (`Cake\Routing\Route\Route`)
 *
 * Note that `Route` does not do any inflections on URLs which will result in
 * inconsistently cased URLs when used with `:plugin`, `:controller` and
 * `:action` markers.
 */
Router::defaultRouteClass(DashedRoute::class);

Router::scope('/', function (RouteBuilder $builder) {
    
    $builder->setExtensions(['html', 'rss', 'xml']);
    
    $builder->connect('/sitemap', ['controller' => 'sitemaps', 'action' => 'index']);
    
    $builder->connect('/', ['controller'=>'workshops', 'action'=>'home']);
    
    $builder->connect('/feed', ['controller' => 'blogs', 'action' => 'feed']); // url for "neuigkeiten"
    $builder->connect('/feed/:blogUrl', ['controller' => 'blogs', 'action' => 'feed'], ['blogUrl' => Configure::read('AppConfig.htmlHelper')->getAdditionalBlogCategoryUrl()]); // sic! no "neuigkeiten"
    
    $builder->connect('/initiativen/newsact/*', ['controller'=>'worknews', 'action'=>'worknewsActivate']);
    $builder->connect('/initiativen/newsunsub/*', ['controller'=>'worknews', 'action'=>'worknewsUnsubscribe']);
    
    $builder->connect('/initiativen/verwalten', ['controller'=>'workshops', 'action'=>'verwalten']);
    $builder->connect('/initiativen/mitmachen', ['controller'=>'workshops', 'action'=>'applyAsUser']);
    $builder->connect('/meine-termine', ['controller'=>'events', 'action'=>'myEvents']);
    $builder->connect('/initiativen/anlegen', ['controller'=>'workshops', 'action'=>'add']);
    $builder->connect('/initiativen/bearbeiten/*', ['controller'=>'workshops', 'action'=>'edit']);
    $builder->connect('/initiativen/loeschen/*', ['controller'=>'workshops', 'action'=>'delete']);
    
    $builder->connect('/orte/*', ['controller'=>'workshops', 'action'=>'all']);
    $builder->connect('/orte', ['controller'=>'workshops', 'action'=>'all']);
    
    $builder->connect('/widgets/integration', ['controller'=>'widgets', 'action'=>'integration']);
    
    $builder->connect('/initiativen/user/refuse/*', ['controller'=>'workshops', 'action'=>'userRefuse']);
    $builder->connect('/initiativen/user/resign/*', ['controller'=>'workshops', 'action'=>'userResign']);
    $builder->connect('/initiativen/user/approve/*', ['controller'=>'workshops', 'action'=>'userApprove']);
    
    $builder->connect('/registrierung/reparaturhelferin', ['controller'=>'users', 'action'=>'registerRepairhelper']);
    $builder->connect('/registrierung/organisatorin', ['controller'=>'users', 'action'=>'registerOrga']);
    $builder->connect('/registrierung', ['controller'=>'users', 'action'=>'register']);
    
    $builder->connect('/users/login', ['controller'=>'users', 'action'=>'login']);
    $builder->connect('/users/logout', ['controller'=>'users', 'action'=>'logout']);
    $builder->connect('/users/welcome', ['controller'=>'users', 'action'=>'welcome']);
    $builder->connect('/users/passwortAendern', ['controller'=>'users', 'action'=>'passwortAendern']);
    $builder->connect('/users/activate/*', ['controller'=>'users', 'action'=>'activate']);
    
    $builder->connect('/users/profile/:id', ['controller'=>'users', 'action'=>'publicProfile'])
        ->setPatterns(['id' => '\d+'])
        ->setPass(['id']);
    $builder->connect('/users/profil/*', ['controller'=>'users', 'action'=>'profil']);
    $builder->connect('/registrierung', ['controller'=>'users', 'action'=>'intro']);
    
    $builder->connect('/reparatur-termine/*', ['controller'=>'events', 'action'=>'all']);
    $builder->connect('/termine/edit/*', ['controller'=>'events', 'action'=>'edit']);
    $builder->connect('/termine/duplicate/*', ['controller'=>'events', 'action'=>'duplicate']);
    $builder->connect('/termine/add/*', ['controller'=>'events', 'action'=>'add']);
    $builder->connect('/termine/delete/*', ['controller'=>'events', 'action'=>'delete']);
    
    $builder->connect('/rss-termine', ['controller' => 'events', 'action' => 'feed']);
    
    $builder->connect('/aktive', ['controller' => 'users', 'action' => 'all']);
    $builder->connect('/aktive/*', ['controller' => 'users', 'action' => 'all']);
    $builder->connect('/kenntnisse', ['controller' => 'skills', 'action' => 'all']);
    
    $builder->connect('/laufzettel/add/*', ['controller'=>'infoSheets', 'action'=>'add']);
    $builder->connect('/laufzettel/edit/*', ['controller'=>'infoSheets', 'action'=>'edit']);
    $builder->connect('/laufzettel/delete/*', ['controller'=>'infoSheets', 'action'=>'delete']);
    
    $builder->connect('/newsletter', ['controller'=>'newsletters', 'action'=>'index']);
    $builder->connect('/newsletter/activate/*', ['controller'=>'newsletters', 'action'=>'activate']);
    $builder->connect('/newsletter/unsubscribe/*', ['controller'=>'newsletters', 'action'=>'unsubscribe']);
    
    $builder->connect('/seite/*', ['controller' => 'pages', 'action' => 'detail']);
    
    $builder->connect('/post/*', ['controller'=>'posts', 'action'=>'detail']);
    $builder->connect('/:blogUrl/*', ['controller'=>'blogs', 'action'=>'detail'], ['blogUrl' => 'neuigkeiten|'.Configure::read('AppConfig.htmlHelper')->getAdditionalBlogCategoryUrl()]);
    
    // für normale cake routings (users controller)
    $builder->connect('/:controller/:action/*');
    
    // short url for initiativen detail
    $builder->connect('/*', ['controller'=>'workshops', 'action'=>'detail']);
    
    /**
     * Connect catchall routes for all controllers.
     *
     * Using the argument `DashedRoute`, the `fallbacks` method is a shortcut for
     *    `$builder->connect('/:controller', ['action' => 'index'], ['routeClass' => 'DashedRoute']);`
     *    `$builder->connect('/:controller/:action/*', [], ['routeClass' => 'DashedRoute']);`
     *
     * Any route class can be used with this method, such as:
     * - DashedRoute
     * - InflectedRoute
     * - Route
     * - Or your own route class
     *
     * You can remove these routes once you've connected the
     * routes you want in your application.
     */
    $builder->fallbacks();
});
    