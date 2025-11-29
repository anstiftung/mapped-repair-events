<?php
declare(strict_types=1);
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


use Cake\TestSuite\Fixture\SchemaLoader;
$schemLoader = new SchemaLoader();
$schemLoader->loadSqlFiles(dirname(__DIR__) . '/config/sql/init/database.sql', 'test', true, true);
$schemLoader->loadSqlFiles(dirname(__DIR__) . '/config/sql/init/phinxlog.sql', 'test', false);

Configure::write('Migrations.backend', 'builtin');

// 1) run migrations
$migrations = new Migrations();
$migrations->migrate(['connection' => 'test', 'plugin' => 'Queue']);
$migrations->migrate(['connection' => 'test']);

$_SERVER['PHP_SELF'] = '/';

Configure::write('AppConfig.adminUserUid', 8);
Configure::write('AppConfig.fundingsEndDateNTime', '2050-02-24 23:59:59');
