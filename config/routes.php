<?php
/**
 * Routes configuration
 *
 * In this file, you set up routes to your controllers and their actions.
 * Routes are very important mechanism that allows you to freely connect
 * different URLs to chosen controllers and their actions (functions).
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
use Cake\Routing\RouteBuilder;
use Cake\Routing\Router;
use Cake\Routing\Route\DashedRoute;

/**
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
 *
 */
Router::defaultRouteClass(DashedRoute::class);

Router::scope('/', function (RouteBuilder $routes) {
    
    $routes->setExtensions(['html', 'rss', 'xml']);
    
    /**
     * Here, we are connecting '/' (base path) to a controller called 'Pages',
     * its action called 'display', and we pass a param to select the view file
     * to use (in this case, src/Template/Pages/home.ctp)...
     */
    $routes->connect('/sitemap', ['controller' => 'sitemaps', 'action' => 'index']);
    
    $routes->connect('/', ['controller'=>'workshops', 'action'=>'home']);
    
    $routes->connect('/feed', ['controller' => 'blogs', 'action' => 'feed']); // url for "neuigkeiten"
    $routes->connect('/feed/:blogUrl', ['controller' => 'blogs', 'action' => 'feed'], ['blogUrl' => Configure::read('AppConfig.htmlHelper')->getAdditionalBlogCategoryUrl()]); // sic! no "neuigkeiten"
    
    $routes->connect('/initiativen/newsact/*', ['controller'=>'worknews', 'action'=>'worknewsActivate']);
    $routes->connect('/initiativen/newsunsub/*', ['controller'=>'worknews', 'action'=>'worknewsUnsubscribe']);
    
    $routes->connect('/initiativen/verwalten', ['controller'=>'workshops', 'action'=>'verwalten']);
    $routes->connect('/initiativen/mitmachen', ['controller'=>'workshops', 'action'=>'applyAsUser']);
    $routes->connect('/meine-termine', ['controller'=>'events', 'action'=>'myEvents']);
    $routes->connect('/initiativen/anlegen', ['controller'=>'workshops', 'action'=>'add']);
    $routes->connect('/initiativen/bearbeiten/*', ['controller'=>'workshops', 'action'=>'edit']);
    $routes->connect('/initiativen/loeschen/*', ['controller'=>'workshops', 'action'=>'delete']);
    
    $routes->connect('/orte/*', ['controller'=>'workshops', 'action'=>'all']);
    $routes->connect('/orte', ['controller'=>'workshops', 'action'=>'all']);
    
    $routes->connect('/widgets/integration', ['controller'=>'widgets', 'action'=>'integration']);
    
    $routes->connect('/initiativen/user/refuse/*', ['controller'=>'workshops', 'action'=>'userRefuse']);
    $routes->connect('/initiativen/user/resign/*', ['controller'=>'workshops', 'action'=>'userResign']);
    $routes->connect('/initiativen/user/approve/*', ['controller'=>'workshops', 'action'=>'userApprove']);
    
    $routes->connect('/registrierung/reparaturhelferin', ['controller'=>'users', 'action'=>'registerRepairhelper']);
    $routes->connect('/registrierung/organisatorin', ['controller'=>'users', 'action'=>'registerOrga']);
    $routes->connect('/registrierung', ['controller'=>'users', 'action'=>'register']);
    
    $routes->connect('/users/login', ['controller'=>'users', 'action'=>'login']);
    $routes->connect('/users/logout', ['controller'=>'users', 'action'=>'logout']);
    $routes->connect('/users/welcome', ['controller'=>'users', 'action'=>'welcome']);
    $routes->connect('/users/passwortAendern', ['controller'=>'users', 'action'=>'passwortAendern']);
    $routes->connect('/users/activate/*', ['controller'=>'users', 'action'=>'activate']);
    
    $routes->connect('/users/profile/:id', ['controller'=>'users', 'action'=>'publicProfile'])
        ->setPatterns(['id' => '\d+'])
        ->setPass(['id']);
    $routes->connect('/users/profil/*', ['controller'=>'users', 'action'=>'profil']);
    $routes->connect('/registrierung', ['controller'=>'users', 'action'=>'intro']);
    
    $routes->connect('/reparatur-termine/*', ['controller'=>'events', 'action'=>'all']);
    $routes->connect('/termine/edit/*', ['controller'=>'events', 'action'=>'edit']);
    $routes->connect('/termine/duplicate/*', ['controller'=>'events', 'action'=>'duplicate']);
    $routes->connect('/termine/add/*', ['controller'=>'events', 'action'=>'add']);
    $routes->connect('/termine/delete/*', ['controller'=>'events', 'action'=>'delete']);
    
    $routes->connect('/rss-termine', ['controller' => 'events', 'action' => 'feed']);
    
    $routes->connect('/laufzettel/add/*', ['controller'=>'infoSheets', 'action'=>'add']);
    $routes->connect('/laufzettel/edit/*', ['controller'=>'infoSheets', 'action'=>'edit']);
    $routes->connect('/laufzettel/delete/*', ['controller'=>'infoSheets', 'action'=>'delete']);
    
    $routes->connect('/newsletter', ['controller'=>'newsletters', 'action'=>'index']);
    $routes->connect('/newsletter/activate/*', ['controller'=>'newsletters', 'action'=>'activate']);
    $routes->connect('/newsletter/unsubscribe/*', ['controller'=>'newsletters', 'action'=>'unsubscribe']);
    
    $routes->connect('/seite/*', ['controller' => 'pages', 'action' => 'detail']);
    
    $routes->connect('/post/*', ['controller'=>'posts', 'action'=>'detail']);
    $routes->connect('/:blogUrl/*', ['controller'=>'blogs', 'action'=>'detail'], ['blogUrl' => 'neuigkeiten|'.Configure::read('AppConfig.htmlHelper')->getAdditionalBlogCategoryUrl()]);
    
    // für normale cake routings (users controller)
    $routes->connect('/:controller/:action/*');
    
    // short url for initiativen detail
    $routes->connect('/*', ['controller'=>'workshops', 'action'=>'detail']);
    
    /**
     * Connect catchall routes for all controllers.
     *
     * Using the argument `DashedRoute`, the `fallbacks` method is a shortcut for
     *    `$routes->connect('/:controller', ['action' => 'index'], ['routeClass' => 'DashedRoute']);`
     *    `$routes->connect('/:controller/:action/*', [], ['routeClass' => 'DashedRoute']);`
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
    $routes->fallbacks(DashedRoute::class);
});
