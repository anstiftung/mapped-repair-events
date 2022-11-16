<?php
/**
 * Test runner bootstrap.
 *
 * Add additional configuration/setup your application needs when running
 * unit tests in this file.
 */
use Cake\Core\Configure;
use Migrations\Migrations;

require dirname(__DIR__) . '/vendor/autoload.php';

require dirname(__DIR__) . '/config/bootstrap.php';

// 2) run new migrations (located in main folder)
//$migrator->run([], false); // causes "Going to drop all tables in this source, and re-apply migrations."
$migrations = new Migrations();
$migrations->migrate(['connection' => 'test']);

$_SERVER['PHP_SELF'] = '/';

Configure::write('AppConfig.adminUserUid', 8);
g
// phpunit with enabled processIsolation sends headers before output
// https://github.com/cakephp/docs/pull/6988
session_id('cli');