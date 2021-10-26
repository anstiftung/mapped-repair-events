<?php
use Cake\Routing\RouteBuilder;
use Cake\Routing\Route\DashedRoute;

return function (RouteBuilder $routes) {
    $routes->plugin('Admin',
        ['path' => '/admin'],
        function (RouteBuilder $routes) {
        	$routes->fallbacks(DashedRoute::class);
    	}
    );
};
